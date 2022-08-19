<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events;

use App\Modules\Core\Library\JobSystem\TransporterInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventDispatcherInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class OnAddJobTransporter implements EventInterface
{

    private array $transporters = [];

    public function event(): static
    {
        return $this;
    }

    public function addJobTransporter(TransporterInterface&EventDispatcherInterface $transporter): static
    {
        $this->transporters[strtolower($transporter->name())] = $transporter;
        return $this;
    }

    /**
     * @return array
     */
    public function getTransporters(): array
    {
        return $this->transporters;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->transporters[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getTransporter(string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->transporters[$name])){
            return $this->transporters[$name];
        }

        throw new \Exception("$name is an unknown transporter name");
    }
    /**
     * @param array $transporters
     */
    public function setTransporters(array $transporters): void
    {
        $this->transporters = $transporters;
    }
}