<?php

namespace Csvtool\Commands;

use Exception;

interface CommandInterface
{
    /**
     * Return an array with the following structure
     * [
     *     'name' => 'command name',
     *     'description' => 'command description',
     *     'args' => [arg1, arg2, --arg3]
     * ]
     * Arguments prefixed with -- are optional.
     */
    public static function getDefinition(): array;

    /**
     * @throws Exception
     */
    public function run();
}