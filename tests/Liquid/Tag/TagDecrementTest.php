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

class TagDecrementTest extends TestCase
{
    /**
     */
    public function testSyntaxError()
    {
        $this->expectException(\YouCan\Liquid\LiquidException::class);

        $this->assertTemplateResult('', '{% decrement %}');
    }

    public function testDecrementNonExistingVariable()
    {
        $this->assertTemplateResult('-1-1', '{% decrement no_such_var %}{{ no_such_var }}');
        $this->assertTemplateResult('-1 -2 -2', '{% decrement var %} {% decrement var %} {{ var }}');
    }

    public function testDecrementSeparateFromAssign()
    {
        $this->assertTemplateResult('-1-243', '{% assign var = 43 %}{% decrement var %}{% decrement var %}{{ var }}');
    }

    public function testDecrementNestedVariable()
    {
        $this->assertTemplateResult('-143', '{% for var in vars %}{% decrement var %}{{ var }}{% endfor %}', ['vars' => [43]]);
    }

    public function testVariableNameContainingNumber()
    {
        $this->assertTemplateResult('-1-2', '{% decrement var123 %}{% decrement var123 %}');
    }
}
