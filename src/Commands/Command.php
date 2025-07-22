<?php

namespace Csvtool\Commands;

abstract class Command implements ICommand
{
    public static function create(string $name, array $options): static
    {
        return new $name($options);
    }

    protected function __construct(protected readonly array $options)
    {
    }
}