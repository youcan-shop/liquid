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

use YouCan\Liquid\Cache\Local;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Template;
use YouCan\Liquid\TestCase;
use YouCan\Liquid\TestFileSystem;

class TagIncludeTest extends TestCase
{
    private $fs;

    /**
     */
    public function testInvalidSyntaxNoTemplateName()
    {
        $this->expectException(\YouCan\Liquid\Exception\ParseException::class);
        $this->expectExceptionMessage('Error in tag');

        $template = new Template();
        $template->setFileSystem($this->fs);
        $template->parse("{% include %}");
    }

    /**
     */
    public function testMissingFilesystem()
    {
        $this->expectException(\YouCan\Liquid\Exception\MissingFilesystemException::class);
        $this->expectExceptionMessage('No file system');

        $template = new Template();
        $template->parse("{% include 'hello' %}");
    }

    public function testInvalidSyntaxInvalidKeyword()
    {
        $template = new Template();
        $template->setFileSystem($this->fs);
        $template->parse("{% include 'hello' no_keyword %}");

        $this->markTestIncomplete("Exception is expected here");
    }

    public function testInvalidSyntaxNoObjectCollection()
    {
        $template = new Template();
        $template->setFileSystem($this->fs);
        $template->parse("{% include 'hello' with %}");

        $this->markTestIncomplete("Exception is expected here");
    }

    public function testIncludeTag()
    {
        $template = new Template();
        $template->setFileSystem($this->fs);

        $template->parse("Outer-{% include 'inner' with 'value' other:23 %}-Outer{% include 'inner' for var other:'loop' %}");

        $output = $template->render(["var" => [1, 2, 3]]);

        $this->assertEquals("Outer-Inner: value23-OuterInner: 1loopInner: 2loopInner: 3loop", $output);
    }

    public function testIncludeTagNoWith()
    {
        $template = new Template();
        $template->setFileSystem($this->fs);

        $template->parse("Outer-{% include 'inner' %}-Outer-{% include 'inner' other:'23' %}");

        $output = $template->render(["inner" => "orig", "var" => [1, 2, 3]]);

        $this->assertEquals("Outer-Inner: orig-Outer-Inner: orig23", $output);
    }

    /**
     * @depends testInvalidSyntaxNoObjectCollection
     */
    public function testWithCache()
    {
        $template = new Template();
        $template->setFileSystem($this->fs);
        $template->setCache(new Local());

        foreach (["Before cache:", "With cache:"] as $type) {
            $template->parse("{{ type }} {% for item in list %}{% include 'example' inner:item %} {% endfor %}{% include 'a' %}");
            $template->render(["inner" => "foo", "list" => [1, 2, 3]], []);
            $this->assertEquals("$type Example: Inner: 1 Example: Inner: 2 (bar)", $template->render(["type" => $type, "inner" => "bar", "list" => [1, 2]]));
        }

        $template->setCache(null);
    }

    public function testIncludeTemplateFile()
    {
        Liquid::set('INCLUDE_PREFIX', '');
        Liquid::set('INCLUDE_SUFFIX', 'tpl');

        $template = new Template(dirname(__DIR__) . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR);
        $template->parse("{% include 'mypartial' %}");
        // template include inserts a new line
        $this->assertEquals("test content" . PHP_EOL, $template->render());
    }

    public function testIncludePassPlainValue()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'inner'   => "[{{ other }}]",
                                          'example' => "({% include 'inner' other:var %})",
                                      ])
        );

        $template->parse("{% include 'example' %}");

        $output = $template->render(["var" => "test"]);
        $this->assertEquals("([test])", $output);
    }

    /**
     */
    public function testIncludePassArrayWithoutIndex()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'inner'   => "[{{ other }}]",
                                          'example' => "({% include 'inner' other:var %})",
                                      ])
        );

        $template->parse("{% include 'example' %}");

        $output = $template->render(["var" => ["a", "b", "c"]]);
        $this->assertEquals("([abc])", $output);
    }

    public function testIncludePassArrayWithIndex()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'inner'   => "[{{ other[0] }}]",
                                          'example' => "({% include 'inner' other:var %})",
                                      ])
        );

        $template->parse("{% include 'example' %}");

        $output = $template->render(["var" => ["a", "b", "c"]]);
        $this->assertEquals("([a])", $output);
    }

    public function testIncludePassObjectValue()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'inner'   => "[{{ other.a }}]",
                                          'example' => "({% include 'inner' other:var %})",
                                      ])
        );

        $template->parse("{% include 'example' %}");

        $output = $template->render(["var" => (object)['a' => 'b']]);
        $this->assertEquals("([b])", $output);
    }

    public function testIncludeWithoutQuotes()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'inner'   => "[{{ other }}]",
                                          'example' => "{%include inner other:var %} ({{var}})",
                                      ])
        );

        $template->parse("{% include example other:var %}");

        $output = $template->render(["var" => "test"]);
        $this->assertEquals("[test] (test)", $output);

        $template->parse("{% include inner %}");

        $output = $template->render(["other" => "test"]);
        $this->assertEquals("[test]", $output);
    }

    /**
     * Render calls in this test shall give same results with cache enabled
     */
    public function testIncludeWithExtends()
    {
        $template = new Template();
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'outer'         => "{% block content %}Content for outer block{% endblock %} / {% block footer %}Footer for outer block{% endblock %}",
                                          'content'       => 'Content for {{ name }} block',
                                          'middle'        => "{% extends 'outer' %}{% block content %}{% include 'content' name:'middle' %}{% endblock %}",
                                          'main'          => "Main: {% extends 'middle' %}{% block footer %}{% include 'footer-top' hello:message %}{% endblock %}",
                                          'footer-bottom' => "{{ name }} with message: {{ hello }}",
                                          'footer-top'    => "Footer top and {% include 'footer-bottom' name:'bottom' %}",
                                      ])
        );

        $template->setCache(new Local());

        foreach (["Before cache", "With cache"] as $type) {
            $this->assertEquals("Block with message: $type", $template->parseFile('footer-bottom')->render(["name" => "Block", "hello" => $type]));
            $this->assertEquals('Content for middle block / Footer for outer block', $template->parseFile('middle')->render());
            $this->assertEquals("Main: Content for middle block / Footer top and bottom with message: $type", $template->parseFile('main')->render(["message" => $type]));

            $template->parse("{% include 'main' hello:message %}");
            $output = $template->render(["message" => $type]);
            $this->assertEquals("Main: Content for middle block / Footer top and bottom with message: $type", $output);
        }

        $template->setCache(null);
    }

    public function testCacheDiscardedIfFileChanges()
    {
        $template = new Template();
        $template->setCache(new Local());

        $content = "[{{ name }}]";
        $template->setFileSystem(
            TestFileSystem::fromArray([
                                          'example' => &$content,
                                      ])
        );

        $template->parse("{% include 'example' %}");
        $output = $template->render(["name" => "Example"]);
        $this->assertEquals("[Example]", $output);

        $content = "<{{ name }}>";
        $template->parse("{% include 'example' %}");
        $output = $template->render(["name" => "Example"]);
        $this->assertEquals("<Example>", $output);
    }

    protected function setUp(): void
    {
        $this->fs = TestFileSystem::fromArray([
                                                  'a'       => "{% include 'b' %}",
                                                  'b'       => "{% include 'c' %}",
                                                  'c'       => "{% include 'd' %}",
                                                  'd'       => '({{ inner }})',
                                                  'inner'   => "Inner: {{ inner }}{{ other }}",
                                                  'example' => "Example: {% include 'inner' %}",
                                              ]);
    }

    protected function tearDown(): void
    {
        // PHP goes nuts unless we unset it
        unset($this->fs);
    }
}
