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

use YouCan\Liquid\Context;
use YouCan\Liquid\Decision;
use YouCan\Liquid\Exception\ParseException;
use YouCan\Liquid\FileSystem;
use YouCan\Liquid\Liquid;
use YouCan\Liquid\Regexp;
use YouCan\Liquid\Template;

/**
 * A switch statement
 *
 * Example:
 *
 *     {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 */
class TagCase extends Decision
{
    /**
     * Stack of nodelists
     *
     * @var array
     */
    public $nodelists;

    /**
     * The nodelist for the else (default) nodelist
     *
     * @var array
     */
    public $elseNodelist;

    /**
     * The left value to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right value to compare
     *
     * @var mixed
     */
    public $right;

    /**
     * @throws ParseException
     */
    public function __construct(Template $template, string $markup, array &$tokens, ?FileSystem $fileSystem = null)
    {
        $this->nodelists = [];
        $this->elseNodelist = [];

        parent::__construct($template, $markup, $tokens, $fileSystem);

        $syntaxRegexp = new Regexp('/' . Liquid::get('QUOTED_FRAGMENT') . '/');

        if ($syntaxRegexp->match($markup)) {
            $this->left = $syntaxRegexp->matches[0];

            return;
        }

        throw new ParseException("Syntax Error in tag 'case' - Valid syntax: case [condition]"); // harry
    }

    /**
     * Pushes the last nodelist onto the stack
     */
    public function endTag()
    {
        $this->pushNodelist();
    }

    /**
     * Pushes the current right value and nodelist into the nodelist stack
     */
    public function pushNodelist()
    {
        if (!is_null($this->right)) {
            $this->nodelists[] = [$this->right, $this->nodelist];
        }
    }

    /**
     * Unknown tag handler
     *
     * @param string $tag
     * @param string $params
     * @param array $tokens
     *
     * @throws \YouCan\Liquid\Exception\ParseException
     */
    public function unknownTag($tag, $params, array $tokens)
    {
        switch ($tag) {
            case 'when':
                $whenSyntax = preg_match_all('/(?<=,|or|^)\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')/', $params, $matches);
                // push the current nodelist onto the stack and prepare for a new one
                if ($whenSyntax) {
                    $this->pushNodelist();
                    $this->right = $matches[1];
                    $this->nodelist = [];
                } else {
                    throw new ParseException("Syntax Error in tag 'case' - Valid when condition: when [condition]"); // harry
                }
                break;

            case 'else':
                // push the last nodelist onto the stack and prepare to receive the else nodes
                $this->pushNodelist();
                $this->right = null;
                $this->elseNodelist = &$this->nodelist;
                $this->nodelist = [];
                break;

            default:
                parent::unknownTag($tag, $params, $tokens);
        }
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        $output = ''; // array();
        $runElseBlock = true;

        foreach ($this->nodelists as $data) {
            [$right, $nodelist] = $data;

            foreach ($right as $var) {
                if ($this->equalVariables($this->left, $var, $context)) {
                    $runElseBlock = false;

                    $context->push();
                    $output .= $this->renderAll($nodelist, $context);
                    $context->pop();

                    break;
                }
            }
        }

        if ($runElseBlock) {
            $context->push();
            $output .= $this->renderAll($this->elseNodelist, $context);
            $context->pop();
        }

        return $output;
    }
}
