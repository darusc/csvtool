<?php

namespace Csvtool\Commands;

abstract class Command implements ICommand
{
    public static function create(string $name, array $args): static
    {
        return new $name($args);
    }

    protected function __construct(protected readonly array $args)
    {
    }
}