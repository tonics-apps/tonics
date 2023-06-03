<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Events;

use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudServerEvent implements EventInterface
{
    private array $cloudServers = [];

    public function event(): static
    {
        return $this;
    }

    public function addCloudServerHandler(CloudServerInterface $cloudServer): static
    {
        $this->cloudServers[strtolower($cloudServer->name())] = $cloudServer;
        return $this;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->cloudServers[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getCloudServerHandler(string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->cloudServers[$name])){
            return $this->cloudServers[$name];
        }

        throw new \Exception("$name is an unknown payment handler name");
    }

    /**
     * @return array
     */
    public function getCloudServers(): array
    {
        return $this->cloudServers;
    }

    /**
     * @param array $cloudServers
     */
    public function setCloudServers(array $cloudServers): void
    {
        $this->cloudServers = $cloudServers;
    }
}