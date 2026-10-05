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
 * Used to increment a counter into a template
 *
 * Example:
 *
 *     {% increment value %}
 *
 * @author Viorel Dram
 */
class TagIncrement extends AbstractTag
{
    /**
     * Name of the variable to increment
     *
     * @var string
     */
    private $toIncrement;

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
            $this->toIncrement = $syntax->matches[0];
        } else {
            throw new ParseException("Syntax Error in 'increment' - Valid syntax: increment [var]");
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
        $value = $context->environments[0][$this->toIncrement] ?? 0;
        $context->environments[0][$this->toIncrement] = $value + 1;

        return $value;
    }
}
