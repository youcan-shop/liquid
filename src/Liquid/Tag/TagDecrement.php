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

/**
 * Used to decrement a counter into a template
 *
 * Example:
 *
 *     {% decrement value %}
 *
 * @author Viorel Dram
 */
class TagDecrement extends AbstractTag
{
    /**
     * Name of the variable to decrement
     *
     * @var int
     */
    private $toDecrement;

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
        $syntax = new Regexp('/(' . Liquid::get('VARIABLE_NAME') . ')/');

        if ($syntax->match($markup)) {
            $this->toDecrement = $syntax->matches[0];
        } else {
            throw new ParseException("Syntax Error in 'decrement' - Valid syntax: decrement [var]");
        }
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     */
    public function render(Context $context)
    {
        // if the value is not set in the environment check to see if it
        // exists in the context, and if not set it to 0
        if (!isset($context->environments[0][$this->toDecrement])) {
            // check for a context value
            $fromContext = $context->get($this->toDecrement);

            // we already have a value in the context
            $context->environments[0][$this->toDecrement] = (null !== $fromContext) ? $fromContext : 0;
        }

        // decrement the environment value
        $context->environments[0][$this->toDecrement]--;

        return '';
    }
}
