<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Commands;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\ForkProcessTrait;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * Start up with: `php bin/console --run --onStartUp=parallel` which would spin up the core command to run on startupClI,
 * it is important to parallelize as some command might take longer than others, if you don't want parallel, then do:
 *
 * <br>
 * `php bin/console --run --onStartUp`
 */
class OnStartUpCLI implements ConsoleCommand, EventInterface
{
    use ConsoleColor, ForkProcessTrait;

    private array $classes = [];

    public function required (): array
    {
        return [
            "--run",
            "--onStartUp",
        ];
    }

    /**
     * Here is a breakdown of how the run command works:
     *
     * - The first line calls the cleanHandleZombieProcess, this deals with properly cleaning of child process that can clean properly, which in turns
     * avoids zombie processes. Note: You do not need to use the cleanHandleZombieProcess in any other classes that is also doing their own fork, as long as
     * they are been spunned from this class, it would handle the zombie cleaning in all of them, wink.
     *
     * - The `$event` variable dispatches an OnStartUpCLI event using the `event()` function, this is used to gather the command that should be run on `StartUp`.
     * The `$helper` variable gets a helper instance using the `helper()` function.
     * The `$parallel` variable checks whether the `--onStartUp` option is set to 'parallel'.
     *
     * - The `cli_set_process_title()` function sets the title of the parent process to the name of the class.
     *
     * - The `$pIDS` array is used to keep track of the child process IDs.
     *
     * - The foreach loop iterates through each command class in the `OnStartUpCLI` event.
     * It checks whether the command class implements the HandlerInterface and ConsoleCommand interfaces.
     * If it does, it creates a new instance of the class and runs the `run()` method of the command.
     * If the `$parallel` variable is TRUE, it forks a child process to run the command.
     *
     * - The `pcntl_alarm()` function is used to set a timer that sends a `SIGALRM` signal to the parent process after a specified number of seconds (1 hour in our case).
     * This is done to prevent memory build-up in long-running tasks.
     * When the `SIGALRM` signal is received by the parent process, it terminates any running child processes using the `SIGKILL` signal.
     *
     * - The while loop that follows waits for child processes to complete or for the timeout to occur.
     * When the SIGALRM signal is received, the loop will exit and the parent process will terminate any remaining child processes.
     * It is important to note that the SIGALRM signal is handled by the default signal handler, which terminates the process.
     * Therefore, the code does not explicitly define a signal handler for SIGALRM.
     *
     * - The `pcntl_alarm()` function cancels the alarm when the loop completes. and the script exit at this point
     *
     * <br>
     * More: How does the PHP terminates all remaining child process when SIGALRM is received?
     *
     * - When the parent process receives the SIGALRM signal, it triggers the default signal handler for that signal, which in turn terminates the process.
     *
     * - When the parent process terminates, any child processes that are still running become orphaned processes. Orphaned processes are adopted by the init process (process ID 1)
     * or sub-init process depending on how the system is configured,
     * which is responsible for cleaning up any orphaned processes.
     * The init process automatically sends the SIGTERM signal to any orphaned processes, which causes them to terminate
     * (off course, the child can handle the SIGTERM signal to clean up before terminating which is what I do in child processes ).
     *
     * - In this script, when the parent process is terminated by the SIGALRM signal,
     * any remaining child processes become orphaned processes and are automatically terminated by the init process.
     * This is how PHP terminates all remaining child processes when the SIGALRM signal is received.
     *
     *
     * @throws \Exception
     */
    public function run (array $commandOptions): void
    {
        $this->cleanHandleZombieProcess();

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
                $command = container()->get($class);

                $this->infoMessage("Running $class");
                if ($parallel) {
                    $helper->fork(
                        onChild: function () use ($class, $command) {
                            cli_set_process_title("$class");
                            $command->run([]);
                        },
                        onParent: function ($pid) use (&$pIDS) {
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
            $pID = pcntl_waitpid(-1, $status);
            if ($pID > 0) {
                $index = array_search($pID, $pIDS);
                if ($index !== false) {
                    unset($pIDS[$index]);
                }
            }
        }

        // Cancel the alarm
        pcntl_alarm(0);
    }

    public function addClass (string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    public function event (): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getClasses (): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses (array $classes): void
    {
        $this->classes = $classes;
    }
}