<?php

namespace YouCan\Liquid;

use YouCan\Liquid\Exception\RenderException;

class TemplateLineTest extends TestCase
{
    public function parseErrors(): array
    {
        return [
            'first line' => ["{% foo %}", 1],
            'after raw and comment' => ["a\n{% raw %}\n{{ x }}\n{% endraw %}\n{% comment %}\nb\nc\n{% endcomment %}\n{% foo %}", 9],
            'nested blocks' => ["{% if a %}\n  {% for x in y %}\n    {% foo %}\n  {% endfor %}\n{% endif %}", 3],
            'never closed' => ["\n\n{% if a %}\nx", 3],
            'unexpected else' => ["{% for x in y %}{% endfor %}\n{% capture a %}\n{% else %}{% endcapture %}", 3],
            'invalid markup' => ["a\n\n{% assign %}", 3],
        ];
    }

    /**
     * @dataProvider parseErrors
     */
    public function testParseErrorLine(string $source, int $line)
    {
        try {
            (new Template())->parse($source);
        } catch (LiquidException $e) {
            $this->assertSame($line, $e->getTemplateLine());

            return;
        }

        $this->fail('Expected a parse error');
    }

    public function testRenderErrorLine()
    {
        $this->assertSame(4, $this->renderErrorLine("a\n{{ x }}\n{% if true %}\n{{ x | boom }}\n{% endif %}"));
    }

    public function testNestedDocumentKeepsItsLine()
    {
        $fs = TestFileSystem::fromArray(['inner' => "\n{% foo %}", 'boom' => "\n\n{{ x | boom }}"]);

        $template = new Template();
        $template->setFileSystem($fs);

        try {
            $template->parse("\n\n\n{% include 'inner' %}");
            $this->fail('Expected a parse error');
        } catch (LiquidException $e) {
            $this->assertSame(2, $e->getTemplateLine());
        }

        $this->assertSame(3, $this->renderErrorLine("\n\n\n\n{% include 'boom' %}", $fs));
    }

    public function testErrorHandlerReplacesFailingNode()
    {
        $this->assertSame("a\n[nope@2]\nb", $this->renderWithHandler("a\n{{ x | fail }}\nb"));
    }

    public function testErrorHandlerGetsIncludedFileLine()
    {
        $fs = TestFileSystem::fromArray(['inner' => "x\n{{ y | fail }}"]);

        $this->assertSame("\n\n\nx\n[nope@2]|z", $this->renderWithHandler("\n\n\n{% include 'inner' %}|z", $fs));
    }

    public function testErrorHandlerKeepsBreakAndContinue()
    {
        $source = "{% for i in (1..4) %}{% if i == 2 %}{% continue %}{% endif %}{% if i == 4 %}{% break %}{% endif %}{{ i }}{% endfor %}\n{{ x | fail }}";

        $this->assertSame("13\n[nope@2]", $this->renderWithHandler($source));
    }

    public function testFailingNodeThrowsWithoutHandler()
    {
        $template = new Template();
        $template->registerFilter('fail', fn() => throw new \RuntimeException('nope'));
        $template->parse("a\n{{ x | fail }}");
        $template->render([], null, ['error_handler' => fn() => '']);

        $this->expectException(\RuntimeException::class);
        $template->render();
    }

    private function renderWithHandler(string $source, ?FileSystem $fs = null): string
    {
        $template = new Template();
        if ($fs) {
            $template->setFileSystem($fs);
        }
        $template->registerFilter('fail', fn() => throw new \RuntimeException('nope'));
        $template->parse($source);

        return $template->render([], null, ['error_handler' => fn(\Throwable $e, ?int $line) => "[{$e->getMessage()}@$line]"]);
    }

    private function renderErrorLine(string $source, ?FileSystem $fs = null): ?int
    {
        $template = new Template();
        if ($fs) {
            $template->setFileSystem($fs);
        }
        $template->registerFilter('boom', fn() => throw new RenderException('boom'));
        $template->parse($source);

        try {
            $template->render();
        } catch (LiquidException $e) {
            return $e->getTemplateLine();
        }

        $this->fail('Expected a render error');
    }
}
