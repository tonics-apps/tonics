<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * Start up with: `php bin/console --run --onStartUp=parallel` which would spin up the core command to run on startupClI,
 * it is important to parallelize as some command might take longer than others, if you don't want parallel, then do:
 *
 * <br>
 * `php bin/console --run --onStartUp`
 */
class OnStartUpCLI implements ConsoleCommand, EventInterface
{
    use ConsoleColor;

    private array $classes = [];

    public function required(): array
    {
        return [
            "--run",
            "--onStartUp"
        ];
    }

    public function addClass(string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        pcntl_async_signals(TRUE);

        // Register signal handler for SIGCHLD
        pcntl_signal(SIGCHLD, function ($sigNo) {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG | WUNTRACED)) > 0) {
                // Child process completed
                posix_kill($pid, SIGKILL); // Kill the child process
            }
        });

        /** @var OnStartUpCLI $event */
        $event = event()->dispatch($this);
        $helper = helper();
        $parallel = $commandOptions['--onStartUp'] === 'parallel';

        # Set Parent Title
        cli_set_process_title(get_class($this));

        $pIDS = [];
        foreach ($event->getClasses() as $class) {
            if ($helper->classImplements($class, [HandlerInterface::class, ConsoleCommand::class])) {
                /** @var ConsoleCommand $command */
                $command = new $class;
                $this->infoMessage("Running $class");
                if ($parallel) {
                    $helper->fork(AppConfig::getAppCLIForkLimit(),
                        onChild: function () use ($class, $command) {
                            cli_set_process_title("$class");
                            $command->run([]);
                        },
                        beforeOnChild: function ($pid) use (&$pIDS) {
                            $pIDS[] = $pid;
                        },
                        onForkError: function () {
                            // handle the fork error here for the parent, this is because when a fork error occurs
                            // it propagates to the parent which abruptly stop the script execution
                            $this->errorMessage("Unable to Fork");
                        });
                } else {
                    $command->run([]);
                }
            }
        }

        // Set the timeout for the script
        # reset the script every one hour, this way, we can better prevents memory build up in a long-running task
        pcntl_alarm(3600);

        // Wait for the child processes to complete or for the timeout to occur
        while (count($pIDS) > 0) {
            $pid = pcntl_waitpid(-1, $status);
            if ($pid > 0) {
                $index = array_search($pid, $pIDS);
                if ($index !== false) {
                    unset($pIDS[$index]);
                }
            }
        }

        // Cancel the alarm
        pcntl_alarm(0);
    }

    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }
}