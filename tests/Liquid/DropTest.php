<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid;

class ContextDrop extends Drop
{
    public function beforeMethod($method)
    {
        return $this->context->get($method);
    }
}

class TextDrop extends Drop
{
    public function get_array()
    {
        return ['text1', 'text2'];
    }

    public function text()
    {
        return 'text1';
    }
}

class CatchallDrop extends Drop
{
    public function beforeMethod($method)
    {
        return 'method: ' . $method;
    }
}

class ProductDrop extends Drop
{
    public function top_sales()
    {
        throw new \Exception("worked");
    }

    public function texts()
    {
        return new TextDrop();
    }

    public function catchall()
    {
        return new CatchallDrop();
    }

    public function context()
    {
        return new ContextDrop();
    }

    public function callmenot()
    {
        return "protected";
    }

    public function hasKey($name)
    {
        return $name != 'unknown' && $name != 'false';
    }
}

class DropTest extends TestCase
{
    /**
     */
    public function testProductDrop()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('worked');

        $template = new Template();
        $template->parse(' {{ product.top_sales }} ');
        $template->render(['product' => new ProductDrop()]);
    }

    public function testNoKeyDrop()
    {
        $template = new Template();
        $template->parse(' {{ product.invalid.unknown }}{{ product.false }} ');
        $output = $template->render(['product' => new ProductDrop()]);
        $this->assertEquals('  ', $output);
    }

    public function testTextDrop()
    {
        $template = new Template();
        $template->parse(' {{ product.texts.text }} ');
        $output = $template->render(['product' => new ProductDrop()]);
        $this->assertEquals(' text1 ', $output);

        $template = new Template();
        $template->parse(' {{ product.catchall.unknown }} ');
        $output = $template->render(['product' => new ProductDrop()]);
        $this->assertEquals(' method: unknown ', $output);
    }

    public function testTextArrayDrop()
    {
        $template = new Template();
        $template->parse('{% for text in product.texts.get_array %} {{text}} {% endfor %}');
        $output = $template->render(['product' => new ProductDrop()]);

        $this->assertEquals(' text1  text2 ', $output);
    }

    public function testContextDrop()
    {
        $template = new Template();
        $template->parse(' {{ context.bar }} ');
        $output = $template->render(['context' => new ContextDrop(), 'bar' => 'carrot']);
        $this->assertEquals(' carrot ', $output);
    }

    public function testNestedContextDrop()
    {
        $template = new Template();
        $template->parse(' {{ product.context.foo }} ');
        $output = $template->render(['product' => new ProductDrop(), 'foo' => 'monkey']);
        $this->assertEquals(' monkey ', $output);
    }

    public function testToString()
    {
        $this->assertSame('ProductDrop', strval(new ProductDrop()));
    }

    public function testOutputHasNoPhpNames()
    {
        $template = new Template();
        $template->parse('[{{ drop }}|{{ list }}|{{ object }}|{{ closure }}]');

        $output = $template->render([
            'drop'    => new ProductDrop(),
            'list'    => new \ArrayObject(['a', new \stdClass(), 'b']),
            'object'  => new \stdClass(),
            'closure' => fn() => 1,
        ]);

        $this->assertSame('[ProductDrop|astdClassb|stdClass|Closure]', $output);

        $this->expectExceptionMessage('invalid arguments for the `split` filter');
        $template->parse('{{ "a" | split: list }}')->render(['list' => ['b']]);
    }

    public function testNumbersAndBooleansPrintAsInLiquid()
    {
        $template = new Template();
        $template->parse('{{ 1 | plus: 1 }}|{{ 1.5 | plus: 0.5 }}|{{ 0.1 | plus: 0.2 }}|{{ 7 | divided_by: 2 }}|{{ -7 | modulo: 3 }}|{{ 2.5 | round }}|{{ 3.14159 | round: 2 }}|{{ t }}|{{ f }}|{{ n }}');

        $this->assertSame('2|2.0|0.3|3|2|3|3.14|true|false|', $template->render(['t' => true, 'f' => false, 'n' => null]));
    }
}
