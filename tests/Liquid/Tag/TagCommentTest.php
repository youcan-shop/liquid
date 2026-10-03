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

class TagCommentTest extends TestCase
{
    public function testHasABlockWhichDoesNothing()
    {
        $this->assertTemplateResult(
            "the comment block should be removed  .. right?",
            "the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?",
        );

        $this->assertTemplateResult('', '{%comment%}{%endcomment%}');
        $this->assertTemplateResult('', '{%comment%}{% endcomment %}');
        $this->assertTemplateResult('', '{% comment %}{%endcomment%}');
        $this->assertTemplateResult('', '{% comment %}{% endcomment %}');
        $this->assertTemplateResult('', '{%comment%}comment{%endcomment%}');
        $this->assertTemplateResult('', '{% comment %}comment{% endcomment %}');

        $this->assertTemplateResult('foobar', 'foo{%comment%}comment{%endcomment%}bar');
        $this->assertTemplateResult('foobar', 'foo{% comment %}comment{% endcomment %}bar');
        $this->assertTemplateResult('foobar', 'foo{%comment%} comment {%endcomment%}bar');
        $this->assertTemplateResult('foobar', 'foo{% comment %} comment {% endcomment %}bar');

        $this->assertTemplateResult('foo  bar', 'foo {%comment%} {%endcomment%} bar');
        $this->assertTemplateResult('foo  bar', 'foo {%comment%}comment{%endcomment%} bar');
        $this->assertTemplateResult('foo  bar', 'foo {%comment%} comment {%endcomment%} bar');

        $this->assertTemplateResult('foobar', 'foo{%comment%} {%endcomment%}bar');
    }

    public function testInlineCommentRendersNothing()
    {
        $this->assertTemplateResult('foobar', 'foo{% # a comment %}bar');
        $this->assertTemplateResult('foobar', 'foo{%# a comment %}bar');
        $this->assertTemplateResult('foobar', "foo {%- # a comment -%}\n bar");
        $this->assertTemplateResult('foobar', "foo{% # one\n  # two %}bar");
        $this->assertTemplateResult('foo', '{% # {{ x }} %}foo');
        $this->assertTemplateResult('yes', '{% if true %}{% # inside a block %}yes{% endif %}');
    }
}
