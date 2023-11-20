<?php

namespace YouCan\Liquid;

interface InvokableFilter
{
    public function __construct(Template $template);

    public function name(): string;

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function __invoke(...$args);
}
