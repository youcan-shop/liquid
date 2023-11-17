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

class CustomFiltersTest extends TestCase
{
    /**
     * The current context
     *
     * @var Context
     */
    public $context;

    public function testSortKey()
    {
        $data = [
            [
                [],
                [],
            ],
            [
                ['b' => 1, 'c' => 5, 'a' => 3, 'z' => 4, 'h' => 2],
                ['a' => 3, 'b' => 1, 'c' => 5, 'h' => 2, 'z' => 4],
            ],
        ];

        foreach ($data as $item) {
            $this->assertEquals($item[1], CustomFilters::sort_key($item[0]));
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new Context();
    }
}
