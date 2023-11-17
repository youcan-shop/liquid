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

use YouCan\Liquid\Cache\File;
use YouCan\Liquid\FileSystem\Virtual;

class VirtualFileSystemTest extends TestCase
{
    /**
     */
    public function testInvalidCallback()
    {
        $this->expectException(\YouCan\Liquid\LiquidException::class);
        $this->expectExceptionMessage('Not a callback');

        new Virtual('');
    }

    public function testReadTemplateFile()
    {
        $fs = new Virtual(function ($templatePath) {
            if ($templatePath == 'foo') {
                return "Contents of foo";
            }

            if ($templatePath == 'bar') {
                return "Bar";
            }

            return '';
        });

        $this->assertEquals('Contents of foo', $fs->readTemplateFile('foo'));
        $this->assertEquals('Bar', $fs->readTemplateFile('bar'));
        $this->assertEquals('', $fs->readTemplateFile('nothing'));
    }

    /**
     */
    public function testWithFileCache()
    {
        $this->expectException(\YouCan\Liquid\LiquidException::class);
        $this->expectExceptionMessage('cannot be used with a serializing cache');

        $template = new Template();
        $template->setFileSystem(
            new Virtual(function ($templatePath) {
                return '';
            })
        );
        $template->setCache(
            new File([
                         'cache_dir' => __DIR__,
                     ])
        );
        $template->parse("Hello");
    }

    public function virtualFileSystemCallback($templatePath)
    {
        return 'OK';
    }

    public function testWithRegularCallback()
    {
        $template = new Template();
        $template->setFileSystem(new Virtual([$this, 'virtualFileSystemCallback'], true));
        $template->setCache(
            new File([
                         'cache_dir' => __DIR__ . '/cache_dir/',
                     ])
        );

        try {
            $template->parse("Test: {% include 'hello' %}");
        } catch (\Throwable $e) {
            $this->assertStringContainsString("Serialization of 'DOMDocument' is not allowed", $e->getMessage());
            $this->markTestIncomplete();
        }
        $this->assertEquals('Test: OK', $template->render());
    }
}
