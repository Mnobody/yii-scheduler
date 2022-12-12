<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Execute;

use Exception;
use Mnobody\Scheduler\ValueObject\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandExecutor
{
    private const SUCCESS_EXIT_CODE = 0;

    public function execute(Application $application, Command $command, ?OutputInterface $output = null): void
    {
        $application->setAutoExit(false);

        $exitCode = $application->run(
            new ArrayInput(
                array_merge(
                    ['command' => $command->getCommand()],
                    $command->getParameters()
                )
            ),
            $output ?: new NullOutput()
        );

        if ($exitCode !== self::SUCCESS_EXIT_CODE) {

            $application->setAutoExit(true);

            $message = sprintf('The command terminated with an error code: %u.', $exitCode);

            $output->writeln(sprintf("<error>%s</error>", $message));

            throw new Exception($message, $exitCode);
        }
    }
}
