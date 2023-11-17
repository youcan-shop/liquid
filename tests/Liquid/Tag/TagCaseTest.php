<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;

class Stringable
{
    public function __toString()
    {
        return "100";
    }
}

class HasToLiquid
{
    public function toLiquid()
    {
        return "100";
    }
}

class TagCaseTest extends TestCase
{
    public function testCase()
    {
        $assigns = ['condition' => 2];
        $this->assertTemplateResult(' its 2 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

        $assigns = ['condition' => 1];
        $this->assertTemplateResult(' its 1 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

        $assigns = ['condition' => 3];
        $this->assertTemplateResult('', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

        $assigns = ['condition' => "string here"];
        $this->assertTemplateResult(' hit ', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);

        $assigns = ['condition' => "bad string here"];
        $this->assertTemplateResult('', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);
    }

    public function multipleConditionsProvider()
    {
        yield [
            [],
            '{% assign handle = "apple" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie", "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is not a cake nor a cookie',
        ];
        yield [
            [],
            '{% assign handle = "cake" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie", "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cake',
        ];
        yield [
            [],
            '{% assign handle = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie", "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
        yield [
            [],
            '{% assign handle = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie" ,"biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
        yield [
            [],
            '{% assign handle = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie" or "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
        yield [
            [],
            '{% assign handle = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when "cookie"or"biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
        yield [
            ['condition' => 'cookie'],
            '{% assign handle = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when condition, "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
        yield [
            [],
            '{% assign handle = "cookie" %}{% assign condition = "cookie" %}{% case handle %}{% when "cake" %}This is a cake{% when condition, "biscuit" %}This is a cookie{% else %}This is not a cake nor a cookie{% endcase %}',
            'This is a cookie',
        ];
    }

    /**
     * @dataProvider multipleConditionsProvider
     */
    public function testMultipleConditions(array $assigns, string $test, string $expected)
    {
        $this->assertTemplateResult($expected, $test, $assigns);
    }

    public function testCaseWithElse()
    {
        $assigns = ['condition' => 5];
        $this->assertTemplateResult(' hit ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);

        $assigns = ['condition' => 6];
        $this->assertTemplateResult(' else ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
    }

    /**
     */
    public function testSyntaxErrorCase()
    {
        $this->expectException(\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% case %}{% when 5 %}{% endcase %}');
    }

    /**
     */
    public function testSyntaxErrorWhen()
    {
        $this->expectException(\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% case condition %}{% when %}{% endcase %}');
    }

    /**
     */
    public function testSyntaxErrorEnd()
    {
        $this->expectException(\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% case condition %}{% end %}');
    }

    /**
     */
    public function testObject()
    {
        $this->expectException(\Liquid\Exception\RenderException::class);

        $this->assertTemplateResult('', '{% case variable %}{% when 5 %}{% endcase %}', ['variable' => (object)[]]);
    }

    public function testStringable()
    {
        $this->assertTemplateResult('hit', '{% case variable %}{% when 100 %}hit{% endcase %}', ['variable' => new Stringable()]);
    }

    public function testToLiquid()
    {
        $this->assertTemplateResult('hit', '{% case variable %}{% when 100 %}hit{% endcase %}', ['variable' => new HasToLiquid()]);
    }
}
