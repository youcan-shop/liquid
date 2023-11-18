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
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * Captures the output inside a block and assigns it to a variable
 *
 * Example:
 *
 *     {% capture foo %} bar {% endcapture %}
 */
class TagCapture extends AbstractBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $to;

    /**
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        $syntaxRegexp = new Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];

            parent::__construct($template, $markup, $tokens, $fileSystem);

            return;
        }

        throw new ParseException("Syntax Error in 'capture' - Valid syntax: capture [var] [value]");
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        $output = parent::render($context);

        $context->set($this->to, $output, true);

        return '';
    }
}
