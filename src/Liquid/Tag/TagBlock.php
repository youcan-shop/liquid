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
use YouCan\Liquid\Exception\ParseException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * Marks a section of a template as being reusable.
 *
 * Example:
 *
 *     {% block foo %} bar {% endblock %}
 */
class TagBlock extends AbstractBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private string $block;

    /**
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        $syntaxRegexp = new Regexp('/(\w+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->block = $syntaxRegexp->matches[1];

            parent::__construct($template, $markup, $tokens, $fileSystem);

            return;
        }

        throw new ParseException("Syntax Error in 'block' - Valid syntax: block [name]");
    }
}
