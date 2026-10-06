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

use YouCan\Liquid\TestCase;

class TagDocTest extends TestCase
{
    public function testRendersNothing()
    {
        $this->assertTemplateResult('foobar', 'foo{% doc %} @param {string} title - The card title {% enddoc %}bar');
    }

    public function testDoesNotParseItsContent()
    {
        $this->assertTemplateResult('ab', "a{% doc %}\n@example\n{% render 'card', title: 'x' %}\n{% unknowntag %}\n{% enddoc %}b");
    }
}
