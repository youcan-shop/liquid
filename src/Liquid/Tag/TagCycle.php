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
use YouCan\Liquid\Exception\ParseException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Variable;

/**
 * Cycles between a list of values; calls to the tag will return each value in turn
 *
 * Example:
 *     {%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}
 *
 *     this will return:
 *     one two one
 *
 *     Cycles can also be named, to differentiate between multiple cycle with the same values:
 *     {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}
 *
 *     will return
 *     one one two two
 */
class TagCycle extends AbstractTag
{
    /**
     * @var string The name of the cycle; if none is given one is created using the value list
     */
    private $name;

    /**
     * @var Variable[] The variables to cycle between
     */
    private $variables = [];

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param FileSystem $fileSystem
     *
     * @throws \YouCan\Liquid\Exception\ParseException
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        $simpleSyntax = new Regexp("/" . Liquid::get('QUOTED_FRAGMENT') . "/");
        $namedSyntax = new Regexp("/(" . Liquid::get('QUOTED_FRAGMENT') . ")\s*\:\s*(.*)/");

        if ($namedSyntax->match($markup)) {
            $this->variables = $this->variablesFromString($namedSyntax->matches[2]);
            $this->name = $namedSyntax->matches[1];
        } elseif ($simpleSyntax->match($markup)) {
            $this->variables = $this->variablesFromString($markup);
            $this->name = "'" . implode($this->variables) . "'";
        } else {
            throw new ParseException("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
        }
    }

    /**
     * Extract variables from a string of markup
     *
     * @param string $markup
     *
     * @return array;
     */
    private function variablesFromString($markup)
    {
        $regexp = new Regexp('/\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')\s*/');
        $parts = explode(',', $markup);
        $result = [];

        foreach ($parts as $part) {
            $regexp->match($part);

            if (!empty($regexp->matches[1])) {
                $result[] = $regexp->matches[1];
            }
        }

        return $result;
    }

    /**
     * Renders the tag
     *
     * @return string
     * @var Context $context
     */
    public function render(Context $context)
    {
        $context->push();

        $key = $context->get($this->name);

        if (isset($context->registers['cycle'][$key])) {
            $iteration = $context->registers['cycle'][$key];
        } else {
            $iteration = 0;
        }

        $result = $context->get($this->variables[$iteration]);

        $iteration += 1;

        if ($iteration >= count($this->variables)) {
            $iteration = 0;
        }

        $context->registers['cycle'][$key] = $iteration;

        $context->pop();

        return $result;
    }
}
