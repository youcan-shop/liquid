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

abstract class AbstractTag
{
    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $markup;

    /**
     * Filesystem object is used to load included template files
     *
     * @var FileSystem
     */
    protected $fileSystem;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = [];

    protected Template $template;

    public function __construct(
        Template $template,
        string $markup,
        array &$tokens,
        ?FileSystem $fileSystem = null
    ) {
        $this->template = $template;
        $this->markup = $markup;
        $this->fileSystem = $fileSystem;

        $this->parse($tokens);
    }

    /**
     * Parse the given tokens.
     *
     * @param array $tokens
     */
    public function parse(array &$tokens)
    {
        // Do nothing by default
    }

    /**
     * Render the tag with the given context.
     *
     * @param Context $context
     *
     * @return string
     */
    abstract public function render(Context $context);

    /**
     * Extracts tag attributes from a markup string.
     *
     * @param string $markup
     */
    protected function extractAttributes($markup)
    {
        $this->attributes = [];

        $attributeRegexp = new Regexp(Liquid::get('TAG_ATTRIBUTES'));

        $matches = $attributeRegexp->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Returns the name of the tag.
     *
     * @return string
     */
    protected function name()
    {
        return strtolower(get_class($this));
    }
}
