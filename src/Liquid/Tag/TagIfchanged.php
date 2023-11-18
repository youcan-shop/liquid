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
use YouCan\Liquid\Template;

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

    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        parent::__construct($template, $markup, $tokens, $fileSystem);
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
