<?php

namespace Csvtool\Commands;

interface ICommand
{
    /**
     * Return an array with the following structure
     * [
     *     'name' => 'command name',
     *     'description' => 'command description',
     *     'options' => [
     *         'option1',
     *         'option2'
     *     ]
     * ]
     */
    public static function getDefinition(): array;

    public function run();
}