<?php

namespace Csvtool\Commands;

use Csvtool\Commands\Impl\ColumnRemovalCommand;
use Csvtool\Commands\Impl\ColumnReorderCommand;
use Csvtool\Commands\Impl\ColumnTruncateCommand;
use Csvtool\Commands\Impl\DateReformatCommand;
use Csvtool\Commands\Impl\DecryptCommand;
use Csvtool\Commands\Impl\EncryptCommand;
use Csvtool\Commands\Impl\HeaderCommand;
use Csvtool\Commands\Impl\HelpCommand;
use Csvtool\Commands\Impl\IndexCommand;
use Csvtool\Commands\Impl\JoinFilesCommand;
use Csvtool\Commands\Impl\MergeFilesCommand;
use Csvtool\Commands\Impl\SelectCommand;
use Csvtool\Commands\Impl\SignCommand;
use Csvtool\Commands\Impl\VerifyCommand;
use Csvtool\Services\CSVFileService;
use Exception;

final class CommandDispatcher
{
    /**
     * Maps each action to the corresponding command implementation
     */
    private static array $commandMap = [
        "help" => HelpCommand::class,
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
        "select" => SelectCommand::class,
    ];

    /**
     * Starts executing a command
     * @throws Exception
     */
    public static function dispatch(string $name, array $args): void
    {
        Command::create(self::$commandMap[$name], $args, new CSVFileService())->run();
    }

    public static function getCommandDefinition(string $commandName): CommandDefinition
    {
        return self::$commandMap[$commandName]::getDefinition();
    }

    public static function commandExists(string $name): bool
    {
        return array_key_exists($name, self::$commandMap);
    }
}