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

class TagUnlessTest extends TestCase
{
    public function testTrueEqlTrue()
    {
        $text = " {% unless true == true %} true {% else %} false {% endunless %} ";
        $expected = "  false  ";
        $this->assertTemplateResult($expected, $text);
    }

    public function testTrueNotEqlTrue()
    {
        $text = " {% unless true != true %} true {% else %} false {% endunless %} ";
        $expected = "  true  ";
        $this->assertTemplateResult($expected, $text);
    }

    public function testWithVariable()
    {
        $text = " {% unless variable %} true {% else %} false {% endunless %} ";
        $expected = "  false  ";
        $this->assertTemplateResult($expected, $text, ['variable' => true]);
    }

    public function testForAndUnless()
    {
        $this->assertTemplateResult(
            '0=>yay 0=>yay 1=> ',
            '{% for item in array %}{{ forloop.last }}=>{% unless forloop.last %}yay{% endunless %} {% endfor %}',
            ['array' => [1, 2, 3]]
        );
        $this->assertTemplateResult(
            '1=> 0=>yay 0=>yay ',
            '{% for item in array %}{{ forloop.first }}=>{% unless forloop.first %}yay{% endunless %} {% endfor %}',
            ['array' => [1, 2, 3]]
        );
        $this->assertTemplateResult('0=> 0=> 1=>yay ', '{% for item in array %}{{ forloop.last }}=>{% if forloop.last %}yay{% endif %} {% endfor %}', ['array' => [1, 2, 3]]);
    }
}
