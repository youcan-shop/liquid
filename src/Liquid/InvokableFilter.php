<?php

namespace YouCan\Liquid;

interface InvokableFilter
{
    public function __construct(Template $template);

    public function name(): string;

    public function __invoke(...$args);

}
