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

/**
 * LiquidException class.
 */
class LiquidException extends \Exception
{
    private ?int $templateLine = null;

    public function getTemplateLine(): ?int
    {
        return $this->templateLine;
    }

    public function setTemplateLine(?int $line): static
    {
        $this->templateLine ??= $line;

        return $this;
    }
}
