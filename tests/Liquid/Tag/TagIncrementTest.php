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

class TagIncrementTest extends TestCase
{
    /**
     */
    public function testSyntaxError()
    {
        $this->expectException(\YouCan\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% increment %}');
    }

    /**
     * Undefined variable will become 0
     */
    public function testIncrementNonExistingVariable()
    {
        $this->assertTemplateResult(0, '{% increment no_such_var %}{{ no_such_var }}');
    }

    public function testIncrementVariable()
    {
        $this->assertTemplateResult(42, '{% increment var %}{{ var }}', ['var' => 41]);
    }

    public function testIncrementNestedVariable()
    {
        $this->assertTemplateResult(42, '{% for var in vars %}{% increment var %}{{ var }}{% endfor %}', ['vars' => [41]]);
    }
}
