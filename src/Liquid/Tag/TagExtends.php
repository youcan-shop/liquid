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
 * Extends a template by another one.
 *
 * Example:
 *
 *     {% extends "base" %}
 */
class TagExtends extends AbstractTag
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
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        $regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');

        if ($regex->match($markup) && isset($regex->matches[1])) {
            $this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
        } else {
            throw new ParseException("Error in tag 'extends' - Valid syntax: extends '[template name]'");
        }

        parent::__construct($template, $markup, $tokens, $fileSystem);
    }

    /**
     * @throws MissingFilesystemException
     */
    public function parse(array &$tokens)
    {
        if ($this->fileSystem === null) {
            throw new MissingFilesystemException("No file system");
        }

        // read the source of the template and create a new sub document
        $source = $this->fileSystem->readTemplateFile($this->templateName);

        // tokens in this new document
        $maintokens = Template::tokenize($source);

        $eRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*extends (.*)?' . Liquid::get('TAG_END') . '$/');
        foreach ($maintokens as $maintoken) {
            if ($eRegexp->match($maintoken)) {
                $m = $eRegexp->matches[1];
                break;
            }
        }

        if (isset($m)) {
            $rest = array_merge($maintokens, $tokens);
        } else {
            $childtokens = $this->findBlocks($tokens);

            $blockstartRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');
            $blockendRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*endblock\s*?' . Liquid::get('TAG_END') . '$/');

            $name = null;

            $rest = [];
            $keep = false;

            for ($i = 0; $i < count($maintokens); $i++) {
                if ($blockstartRegexp->match($maintokens[$i])) {
                    $name = $blockstartRegexp->matches[1];

                    if (isset($childtokens[$name])) {
                        $keep = true;
                        array_push($rest, $maintokens[$i]);
                        foreach ($childtokens[$name] as $item) {
                            array_push($rest, $item);
                        }
                    }
                }
                if (!$keep) {
                    array_push($rest, $maintokens[$i]);
                }

                if ($blockendRegexp->match($maintokens[$i]) && $keep === true) {
                    $keep = false;
                    array_push($rest, $maintokens[$i]);
                }
            }
        }

        $cache = Template::getCache();

        if (!$cache) {
            $this->document = new Document($this->template, $rest, $this->fileSystem);

            return;
        }

        $this->hash = md5($source);

        $this->document = $cache->read($this->hash);

        if ($this->document == false || $this->document->hasIncludes() == true) {
            $this->document = new Document($this->template, $rest, $this->fileSystem);
            $cache->write($this->hash, $this->document);
        }
    }

    /**
     * @param array $tokens
     *
     * @return array
     */
    private function findBlocks(array $tokens)
    {
        $blockstartRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');
        $blockendRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*endblock\s*?' . Liquid::get('TAG_END') . '$/');

        $b = [];
        $name = null;

        foreach ($tokens as $token) {
            if ($blockstartRegexp->match($token)) {
                $name = $blockstartRegexp->matches[1];
                $b[$name] = [];
            } elseif ($blockendRegexp->match($token)) {
                $name = null;
            } else {
                if ($name !== null) {
                    array_push($b[$name], $token);
                }
            }
        }

        return $b;
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
        $context->push();
        $result = $this->document->render($context);
        $context->pop();

        return $result;
    }
}
