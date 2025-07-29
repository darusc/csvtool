<?php

namespace Csvtool\Commands;

use Csvtool\Exceptions\MissingArgumentException;
use Csvtool\Services\CSVFileService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Base command class. Provides method for extracting and validating input options.
 * The class is abstract so it is not autowired so it doesn't need to be defined
 * as an actual command with #[AsCommand()]
 */
abstract class CommandBase extends Command
{
    public function __construct(
        protected readonly CSVFileService $fileService
    )
    {
        parent::__construct();
    }

    /**
     * Iterate through the defined options from and for those that
     * are required check they were given
     *
     * @param InputInterface $input
     * @return array Array containing the extracted options
     * @throws MissingArgumentException
     */
    protected function extractOptions(InputInterface $input): array
    {
        $extractedOptions = [];
        $definedOptions = $this->getDefinition()->getOptions();

        foreach ($definedOptions as $option) {
            $name = $option->getName();
            if ($option->isValueRequired() && $input->getOption($name) === null) {
                throw new MissingArgumentException($name);
            }
            $extractedOptions[$name] = $input->getOption($name);
        }

        return $extractedOptions;
    }
}
