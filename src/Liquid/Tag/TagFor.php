<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid\Tag;

use YouCan\Liquid\AbstractBlock;
use YouCan\Liquid\Context;
use YouCan\Liquid\Exception\ParseException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * Loops over an array, assigning the current value to a given variable
 *
 * Example:
 *
 *     {%for item in array%} {{item}} {%endfor%}
 *
 *     With an array of 1, 2, 3, 4, will return 1 2 3 4
 *
 *     or
 *
 *     {%for i in (1..10)%} {{i}} {%endfor%}
 *     {%for i in (1..variable)%} {{i}} {%endfor%}
 *
 */
class TagFor extends AbstractBlock
{
    /**
     * @var array The collection to loop over
     */
    private $collectionName;

    /**
     * @var string The variable name to assign collection elements to
     */
    private $variableName;

    /**
     * @var string The name of the loop, which is a compound of the collection and variable names
     */
    private $name;

    /**
     * @var string
     */
    private $start;

    /**
     * @var string The type of the loop (collection or digit)
     */
    private $type = 'collection';

    private ?array $elseNodelist = null;

    private bool $reversed;

    /**
     * @param Template $template
     * @param string $markup
     * @param array $tokens
     * @param FileSystem|null $fileSystem
     *
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        parent::__construct($template, $markup, $tokens, $fileSystem);

        $this->reversed = (bool) preg_match('/\s+in\s+(?:\(.*?\)|\S+)\s+reversed\b/', $markup);
        $syntaxRegexp = new Regexp('/(\w+)\s+in\s+(' . Liquid::get('VARIABLE_PATH') . ')/');

        if ($syntaxRegexp->match($markup)) {
            $this->variableName = $syntaxRegexp->matches[1];
            $this->collectionName = $syntaxRegexp->matches[2];
            $this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
            $this->extractAttributes($markup);
        } else {
            $syntaxRegexp = new Regexp('/(\w+)\s+in\s+\((\d+|' . Liquid::get('VARIABLE_NAME') . ')\s*\.\.\s*(\d+|' . Liquid::get('VARIABLE_NAME') . ')\)/');
            if ($syntaxRegexp->match($markup)) {
                $this->type = 'digit';
                $this->variableName = $syntaxRegexp->matches[1];
                $this->start = $syntaxRegexp->matches[2];
                $this->collectionName = $syntaxRegexp->matches[3];
                $this->name = $syntaxRegexp->matches[1] . '-digit';
                $this->extractAttributes($markup);
            } else {
                throw new ParseException("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
            }
        }
    }

    protected function unknownTag($tag, $params, array $tokens)
    {
        if ($tag != 'else') {
            parent::unknownTag($tag, $params, $tokens);
        }

        $this->elseNodelist = $this->nodelist;
        $this->nodelist = [];
    }

    protected function endTag()
    {
        if ($this->elseNodelist !== null) {
            [$this->nodelist, $this->elseNodelist] = [$this->elseNodelist, $this->nodelist];
        }
    }

    private function renderElse(Context $context)
    {
        return $this->renderAll($this->elseNodelist ?? [], $context);
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return null|string
     */
    public function render(Context $context)
    {
        if (!isset($context->registers['for'])) {
            $context->registers['for'] = [];
        }

        if ($this->type == 'digit') {
            return $this->renderDigit($context);
        }

        // that's the default
        return $this->renderCollection($context);
    }

    private function renderDigit(Context $context)
    {
        $start = $this->start;
        if (!is_integer($this->start)) {
            $start = $context->get($this->start);
        }

        $end = $this->collectionName;
        if (!is_integer($this->collectionName)) {
            $end = $context->get($this->collectionName);
        }

        if ($start > $end) {
            return $this->renderElse($context);
        }

        return $this->renderCollection($context, range((int) $start, (int) $end));
    }

    private function renderCollection(Context $context, $collection = null)
    {
        $collection ??= $context->get($this->collectionName);

        if ($collection instanceof \Generator && !$collection->valid()) {
            return $this->renderElse($context);
        }

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (is_null($collection) || !is_array($collection) || count($collection) == 0) {
            return $this->renderElse($context);
        }

        $range = [0, count($collection)];

        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            $offset = 0;

            if (isset($this->attributes['offset'])) {
                $offset = ($this->attributes['offset'] == 'continue') ? $context->registers['for'][$this->name] : $context->get($this->attributes['offset']);
            }

            $limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
            $rangeEnd = $limit ? $limit : count($collection) - $offset;
            $range = [$offset, $rangeEnd];

            $context->registers['for'][$this->name] = $rangeEnd + $offset;
        }

        $result = '';
        $segment = array_slice($collection, $range[0], $range[1]);
        if (!count($segment)) {
            return $this->renderElse($context);
        }
        if ($this->reversed) {
            $segment = array_reverse($segment);
        }

        $context->push();
        $length = count($segment);

        $index = 0;
        foreach ($segment as $key => $item) {
            $value = is_numeric($key) ? $item : [$key, $item];
            $context->set($this->variableName, $value);
            $context->set('forloop', [
                'name'    => $this->name,
                'length'  => $length,
                'index'   => $index + 1,
                'index0'  => $index,
                'rindex'  => $length - $index,
                'rindex0' => $length - $index - 1,
                'first'   => (int) ($index == 0),
                'last'    => (int) ($index == $length - 1),
            ]);

            $result .= $this->renderAll($this->nodelist, $context);

            $index++;

            if (isset($context->registers['break'])) {
                unset($context->registers['break']);
                break;
            }
            if (isset($context->registers['continue'])) {
                unset($context->registers['continue']);
            }
        }

        $context->pop();

        return $result;
    }
}
