<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid\Cache;

use YouCan\Liquid\TestCase;

class LocalTest extends TestCase
{
    /** @var \YouCan\Liquid\Cache\Local */
    protected $cache;

    public function testNotExists()
    {
        $this->assertFalse($this->cache->exists('no_such_key'));
    }

    public function testReadNotExisting()
    {
        $this->assertFalse($this->cache->read('no_such_key'));
    }

    public function testSetGetFlush()
    {
        $this->assertTrue($this->cache->write('test', 'example'));
        $this->assertSame('example', $this->cache->read('test'));
        $this->assertTrue($this->cache->flush());
        $this->assertFalse($this->cache->read('test'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new Local();
    }
}
