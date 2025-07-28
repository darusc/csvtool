<?php

namespace Csvtool;

use Csvtool\Exceptions\MissingArgumentException;

class ArgumentParser
{
    private const string OPTTION_PATTERN = "/^--([a-zA-Z0-9_-]+)(=(.*))?$/";
    private static array $options = [];

    /**
     * Parses argv using given command options and checks
     * if all the required options are given.
     *
     * The function uses manual parsing and not getopt because
     * it will not work with a positional argument.
     *
     * --param => optional, no value
     *
     * --param: => required, with value
     *
     * @throws MissingArgumentException
     */
    public static function parse(array $argv, array $commandOptions): void
    {
        // Get the given options
        self::$options = self::extractOptions($argv);

        // Check if all required options were given and throw error if there are missing options.
        // Required options end with :
        foreach ($commandOptions as $option) {
            $option = trim($option, "-:");
            if(str_ends_with($option, ":")) {
                if(!array_key_exists($option, self::$options)) {
                    throw new MissingArgumentException($option);
                }
            }
        }
    }

    /**
     * Extracts given options from argv
     */
    private static function extractOptions(array $argv): array
    {
        $options = [];
        foreach ($argv as $arg) {
            // Use a regular expression to split the string into option name and value
            // Options that don't have a value are given true
            preg_match(self::OPTTION_PATTERN, $arg, $matches);

            $option = $matches[1];
            $value = $matches[3] ?? true;

            $options[$option] = $value;
        }
        return $options;
    }


    public static function getOptions(): array
    {
        return self::$options;
    }
}