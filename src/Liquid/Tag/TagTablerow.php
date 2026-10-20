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
use YouCan\Liquid\Exception\RenderException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * Quickly create a table from a collection
 */
class TagTablerow extends AbstractBlock
{
    /**
     * The variable name of the table tag
     *
     * @var string
     */
    public $variableName;

    /**
     * The collection name of the table tags
     *
     * @var string
     */
    public $collectionName;

    /**
     * Additional attributes
     *
     * @var array
     */
    public $attributes;

    private ?array $range = null;

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

        $syntax = new Regexp('/(\w+)\s+in\s+(' . Liquid::get('VARIABLE_PATH') . ')/');
        $range = new Regexp('/(\w+)\s+in\s+\((\d+|' . Liquid::get('VARIABLE_NAME') . ')\s*\.\.\s*(\d+|' . Liquid::get('VARIABLE_NAME') . ')\)/');

        if ($syntax->match($markup)) {
            $this->variableName = $syntax->matches[1];
            $this->collectionName = $syntax->matches[2];

            $this->extractAttributes($markup);
        } elseif ($range->match($markup)) {
            $this->variableName = $range->matches[1];
            $this->range = [$range->matches[2], $range->matches[3]];

            $this->extractAttributes($markup);
        } else {
            throw new ParseException("Syntax Error in 'table_row loop' - Valid syntax: table_row [item] in [collection] cols:3");
        }
    }

    /**
     * Renders the current node
     *
     * @param Context $context
     *
     * @throws \YouCan\Liquid\Exception\RenderException
     * @return string
     */
    public function render(Context $context)
    {
        [$start, $end] = array_map(fn($value) => (int) $context->get($value), $this->range ?? [0, 0]);
        $collection = $this->range === null ? $context->get($this->collectionName) : ($start > $end ? [] : range($start, $end));

        if ($collection === null) {
            return '';
        }

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (!is_array($collection)) {
            throw new RenderException("Not an array");
        }

        $offset = isset($this->attributes['offset']) ? (int) $context->get($this->attributes['offset']) : 0;
        $limit = isset($this->attributes['limit']) ? (int) $context->get($this->attributes['limit']) : null;
        $collection = array_slice(array_values($collection), $offset, $limit);
        $length = count($collection);
        $cols = isset($this->attributes['cols']) ? (int) $context->get($this->attributes['cols']) : $length;

        $row = 1;
        $col = 1;
        $result = "<tr class=\"row1\">\n";

        $context->push();

        foreach ($collection as $index => $item) {
            $context->set($this->variableName, $item);
            $context->set('tablerowloop', [
                'length'    => $length,
                'index'     => $index + 1,
                'index0'    => $index,
                'rindex'    => $length - $index,
                'rindex0'   => $length - $index - 1,
                'first'     => $index == 0,
                'last'      => $index == $length - 1,
                'col'       => $col,
                'col0'      => $col - 1,
                'col_first' => $col == 1,
                'col_last'  => $col == $cols,
                'row'       => $row,
            ]);

            $result .= "<td class=\"col$col\">" . $this->renderAll($this->nodelist, $context) . '</td>';

            if (isset($context->registers['break'])) {
                unset($context->registers['break']);
                break;
            }

            unset($context->registers['continue']);

            if ($col == $cols && $index != $length - 1) {
                $result .= "</tr>\n<tr class=\"row" . ($row + 1) . "\">";
            }

            [$row, $col] = $col == $cols ? [$row + 1, 1] : [$row, $col + 1];
        }

        $context->pop();

        return $result . "</tr>\n";
    }
}
