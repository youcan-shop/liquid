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

use YouCan\Liquid\AbstractBlock;
use YouCan\Liquid\Context;
use YouCan\Liquid\FileSystem;

/**
 * Quickly create a table from a collection
 */
class TagIfchanged extends AbstractBlock
{
    /**
     * The last value
     *
     * @var string
     */
    private $lastValue = '';

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param FileSystem $fileSystem
     *
     * @throws \YouCan\Liquid\LiquidException
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        parent::__construct($markup, $tokens, $fileSystem);
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        $output = parent::render($context);

        if ($this->lastValue == $output) {
            return '';
        }
        $this->lastValue = $output;

        return $this->lastValue;
    }
}
