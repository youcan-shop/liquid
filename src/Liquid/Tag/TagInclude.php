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

use YouCan\Liquid\AbstractTag;
use YouCan\Liquid\Context;
use YouCan\Liquid\Document;
use YouCan\Liquid\Exception\MissingFilesystemException;
use YouCan\Liquid\Exception\ParseException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% include 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% include 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% include 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo
 *     with each value of bar
 */
class TagInclude extends AbstractTag
{
    /**
     * @var string The Source Hash
     */
    protected $hash;
    /**
     * @var string The name of the template
     */
    private $templateName;
    /**
     * @var bool True if the variable is a collection
     */
    private $collection;
    /**
     * @var mixed The value to pass to the child template as the template name
     */
    private $variable;
    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        $regex = new Regexp('/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+(' . Liquid::get('QUOTED_FRAGMENT') . '+))?/');

        if (!$regex->match($markup)) {
            throw new ParseException("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }

        $unquoted = (strpos($regex->matches[1], '"') === false && strpos($regex->matches[1], "'") === false);

        $start = 1;
        $len = strlen($regex->matches[1]) - 2;

        if ($unquoted) {
            $start = 0;
            $len = strlen($regex->matches[1]);
        }

        $this->templateName = substr($regex->matches[1], $start, $len);

        if (isset($regex->matches[1])) {
            $this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
            $this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
        }

        $this->extractAttributes($markup);

        parent::__construct($template, $markup, $tokens, $fileSystem);
    }

    /**
     * Parses the tokens
     *
     * @param array $tokens
     *
     * @throws \YouCan\Liquid\Exception\MissingFilesystemException
     */
    public function parse(array &$tokens)
    {
        if ($this->fileSystem === null) {
            throw new MissingFilesystemException("No file system");
        }

        // read the source of the template and create a new sub document
        $source = $this->fileSystem->readTemplateFile($this->templateName);

        $cache = Template::getCache();

        if (!$cache) {
            // tokens in this new document
            $templateTokens = Template::tokenize($source);
            $this->document = new Document($this->template, $templateTokens, $this->fileSystem);

            return;
        }

        $this->hash = md5($source);
        $this->document = $cache->read($this->hash);

        if ($this->document == false || $this->document->hasIncludes() == true) {
            $templateTokens = Template::tokenize($source);
            $this->document = new Document($this->template, $templateTokens, $this->fileSystem);
            $cache->write($this->hash, $this->document);
        }
    }

    /**
     * Check for cached includes; if there are - do not use cache
     *
     * @return boolean
     * @see Document::hasIncludes()
     */
    public function hasIncludes()
    {
        if ($this->document->hasIncludes() == true) {
            return true;
        }

        $source = $this->fileSystem->readTemplateFile($this->templateName);

        if (Template::getCache()->exists(md5($source)) && $this->hash === md5($source)) {
            return false;
        }

        return true;
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        $result = '';
        $variable = $context->get($this->variable);

        $context->push();

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->collection) {
            foreach ($variable as $item) {
                $context->set($this->templateName, $item);
                $result .= $this->document->render($context);
            }
        } else {
            if (!is_null($this->variable)) {
                $context->set($this->templateName, $variable);
            }

            $result .= $this->document->render($context);
        }

        $context->pop();

        return $result;
    }
}
