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
        /** @var OnStartUpCLI $event */
        $event = event()->dispatch($this);
        $helper = helper();
        $parallel = $commandOptions['--onStartUp'] === 'parallel';

        $pIDS = [];
        foreach ($event->getClasses() as $class){
            if ($helper->classImplements($class, [HandlerInterface::class, ConsoleCommand::class])){
                /** @var ConsoleCommand $command */
                $command = new $class;
                $this->infoMessage("Running $class");
                if ($parallel){
                    $helper->fork(AppConfig::getAppCLIForkLimit(), onChild: function () use ($class, $command) {
                        cli_set_process_title("$class");
                        $command->run([]);
                    }, beforeOnChild: function ($pid) use (&$pIDS){
                        $pIDS[] = $pid;
                    });
                } else {
                    $command->run([]);
                }
            }
        }

        # We could have put this in the fork function, but it would wait for the child process to exit making it block
        # putting it here solves that problem...
        foreach ($pIDS as $pID){
            pcntl_waitpid($pID, $status);
            if ($status > 0){
                posix_kill($pID, SIGKILL);
            }
        }
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