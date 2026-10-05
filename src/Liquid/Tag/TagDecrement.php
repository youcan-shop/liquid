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
use YouCan\Liquid\Template;

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
     * @param Template $template
     * @param string $markup
     * @param array $tokens
     * @param FileSystem|null $fileSystem
     *
     * @throws ParseException
     */
    public function __construct(Template $template, $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        parent::__construct($template, $markup, $tokens, $fileSystem);

        $this->template = $template;

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
     * @return int
     */
    public function render(Context $context)
    {
        return $context->environments[0][$this->toDecrement] = ($context->environments[0][$this->toDecrement] ?? 0) - 1;
    }
}
