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

use YouCan\Liquid\Context;

/**
 * Documents a snippet or a block; the content is neither parsed nor rendered
 *
 * Example:
 *
 *     {% doc %} @param {string} title {% enddoc %}
 */
class TagDoc extends TagRaw
{
    public function render(Context $context)
    {
        return '';
    }
}
