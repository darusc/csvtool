<?php

namespace Csvtool;

use Csvtool\Commands\Command;
use Csvtool\Commands\Impl\ColumnRemovalCommand;
use Csvtool\Commands\Impl\ColumnReorderCommand;
use Csvtool\Commands\Impl\DateReformatCommand;
use Csvtool\Commands\Impl\DecryptCommand;
use Csvtool\Commands\Impl\EncryptCommand;
use Csvtool\Commands\Impl\HeaderCommand;
use Csvtool\Commands\Impl\IndexCommand;
use Csvtool\Commands\Impl\ColumnTruncateCommand;
use Csvtool\Commands\Impl\JoinFilesCommand;
use Csvtool\Commands\Impl\MergeFilesCommand;
use Csvtool\Commands\Impl\SignCommand;
use Csvtool\Commands\Impl\VerifyCommand;
use Csvtool\Exceptions\InvalidActionException;
use Csvtool\Exceptions\MissingArgumentException;
use Csvtool\Services\CSVFileService;
use Csvtool\Services\FIleWriterService;
use Csvtool\Services\StreamReaderService;
use InvalidArgumentException;

class Application
{
    /**
     * Maps each action to the corresponding command implementation
     */
    public static array $actionMap = [
        "header" => HeaderCommand::class,
        "index" => IndexCommand::class,
        "reorder" => ColumnReorderCommand::class,
        "rmcol" => ColumnRemovalCommand::class,
        "trunc" => ColumnTruncateCommand::class,
        "refdate" => DateReformatCommand::class,
        "merge" => MergeFilesCommand::class,
        "encrypt" => EncryptCommand::class,
        "decrypt" => DecryptCommand::class,
        "sign" => SignCommand::class,
        "verify" => VerifyCommand::class,
        "join" => JoinFilesCommand::class,
    ];

    /**
     * Attempts to create a new instance of Application.
     * Parses argv to get the action and options to execute.
     * If successful returns the newly created instance
     *
     * @throws InvalidActionException
     * @throws MissingArgumentException
     */
    public static function create(int $argc, array $argv): static
    {
        if ($argc < 2) {
            throw new InvalidArgumentException();
        }

        $action = $argv[1];
        if (!isset(self::$actionMap[$action])) {
            throw new InvalidActionException($action);
        }

        // Get the needed args for the current action
        $neededArgs = static::$actionMap[$action]::getDefinition()['args'];
        // Parse argv to get given options
        $parsedArgs = static::parseOptions($argv, $neededArgs);

        return new static($action, $parsedArgs);
    }

    /**
     * Parse argv and return an array containing all options and their values
     * [$option => $value]
     * @throws MissingArgumentException
     */
    private static function parseOptions(array $argv, array $expected): array
    {
        $parsedArgs = [];

        // The arguments we are interested in start at index 2
        foreach (array_slice($argv, 2) as $index => $arg) {
            // If the current arg doesn't start with -- it is a positional argument => add it at current index
            if (!str_starts_with($arg, '--')) {
                $parsedArgs[$expected[$index]] = $arg;
            } else {
                // If the current arg is optional but the expected arg is not then we miss some arguments
                if (!str_starts_with($expected[$index], '--')) {
                    throw new MissingArgumentException($expected[$index]);
                } else {
                    // Otherwise, get the value of the optional arg and add it to the result
                    $components = explode('=', $arg);
                    $key = trim($components[0], '-:');
                    $value = $components[1] ?? true;
                    $parsedArgs[$key] = $value;
                }
            }
        }

        // Missing argument check if not enough arguments in argv
        $c = count($parsedArgs);
        if ($c < count($expected) && !str_starts_with($expected[$c], '--')) {
            throw new MissingArgumentException($expected[$c]);
        }

        return $parsedArgs;
    }

    private function __construct(
        private readonly string $action,
        private readonly array  $args
    )
    {

    }

    /**
     * Starts executing the command
     */
    public function run(): void
    {
        $command = Command::create(
            static::$actionMap[$this->action],
            $this->args,
            new CSVFileService()
        );
        $command->run();
    }
}