<?php

namespace Csvtool;

use Csvtool\Commands\Command;
use Csvtool\Commands\HeaderCommand;
use InvalidArgumentException;

class Application
{
    /**
     * Maps each action to the corresponding command implementation
     */
    public static $actionMap = [
        "header" => HeaderCommand::class,
    ];

    /**
     * Attempts to create a new instance of Application.
     * Parses argv to get the action and options to execute.
     * If successful returns the newly created instance,
     * otherwise throws InvalidArgumentException
     */
    public static function create(int $argc, array $argv): static
    {
        if ($argc < 2) {
            throw new InvalidArgumentException();
        }

        $action = $argv[1];
        if (!isset(self::$actionMap[$action])) {
            throw new InvalidArgumentException("Unknown action: $action");
        }

        // Get the needed options for the current action
        $options = static::$actionMap[$action]::getDefinition()['options'];

        // Parse argv to get given options
        $parsedOptions = static::parseOptions($argv);

        foreach ($options as $option) {
            if (!isset($parsedOptions[$option])) {
                throw new InvalidArgumentException("Missing option '$option'");
            }
        }

        return new static($action, $parsedOptions);
    }

    /**
     * Parse argv and return an array containing all options and their values
     * [$option => $value]
     */
    private static function parseOptions(array $argv): array
    {
        $options = [];
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $components = explode('=', substr($arg, 2));
                $key = $components[0];
                $value = $components[1] ?? true;
                $options[$key] = $value;
            }
        }
        return $options;
    }

    private function __construct(
        private readonly string $action,
        private readonly array  $options
    )
    {

    }

    /**
     * Starts executing the command
     */
    public function run(): void
    {
        $command = Command::create(static::$actionMap[$this->action], $this->options);
        $command->run();
    }
}