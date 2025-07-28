<?php

namespace Csvtool\Commands;

final readonly class CommandDefinition
{
    public function __construct(
        public string $name,
        public string $description,
        public array  $args,
    )
    {

    }
}