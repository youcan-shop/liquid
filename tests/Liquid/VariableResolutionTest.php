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

class VariableResolutionTest extends TestCase
{
    public function testSimpleVariable()
    {
        $template = new Template();
        $template->parse("{{test}}");
        $this->assertEquals('worked', $template->render(['test' => 'worked']));
    }

    public function testSimpleWithWhitespaces()
    {
        $template = new Template();

        $template->parse('  {{ test }}  ');
        $this->assertEquals('  worked  ', $template->render(['test' => 'worked']));
        $this->assertEquals('  worked wonderfully  ', $template->render(['test' => 'worked wonderfully']));
    }

    public function testIgnoreUnknown()
    {
        $template = new Template();

        $template->parse('{{ test }}');
        $this->assertEquals('', $template->render());
    }

    public function testLineBreak()
    {
        $template = new Template();

        $template->parse("{{ test |\n strip_html }}");
        $this->assertEquals('worked', $template->render(['test' => '<b>worked</b>']));
    }

    public function testArrayScoping()
    {
        $template = new Template();

        $template->parse('{{ test.test }}');
        $this->assertEquals('worked', $template->render(['test' => ['test' => 'worked']]));
    }

    public function testVariableArrayIndices()
    {
        $template = new Template();

        $template->parse("{% assign days = 'Mon,Tue,Wed,Thu,Fri,Sat,Sun' | split: ',' %}{% for i in (0..6) %}{{ days[i] }} {% endfor %}");
        $this->assertEquals('Mon Tue Wed Thu Fri Sat Sun ', $template->render());
    }

    public function testQuotedArrayKeys()
    {
        $assigns = ['c' => ['all' => ['n' => 'A'], 'my-x' => ['n' => 'X'], 'L' => [['n' => 'zero'], ['n' => 'one']]], 'k' => 'my-x', 'i' => 1];

        $this->assertTemplateResult('A', "{{ c['all'].n }}", $assigns);
        $this->assertTemplateResult('A', '{{ c["all"].n }}', $assigns);
        $this->assertTemplateResult('X', "{{ c['my-x'].n }}", $assigns);
        $this->assertTemplateResult('X', '{{ c[k].n }}', $assigns);
        $this->assertTemplateResult('one', "{{ c['L'][i].n }}", $assigns);
        $this->assertTemplateResult('AX', "{{ c['all'].n | append: c['my-x'].n }}", $assigns);
        $this->assertTemplateResult('y', "{% if c['all'].n == 'A' %}y{% endif %}", $assigns);
        $this->assertTemplateResult('', "{{ c['none'].n }}", $assigns);
    }
}
