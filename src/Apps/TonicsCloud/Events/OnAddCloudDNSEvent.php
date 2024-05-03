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

use App\Apps\TonicsCloud\Interfaces\CloudDNSInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudDNSEvent implements EventInterface
{
    private array $cloudDNS = [];

    public function event(): static
    {
        return $this;
    }

    public function addCloudServerHandler(CloudDNSInterface $cloudServer): static
    {
        $this->cloudDNS[strtolower($cloudServer->name())] = $cloudServer;
        return $this;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->cloudDNS[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getCloudDNSHandler(string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->cloudDNS[$name])){
            return $this->cloudDNS[$name];
        }

        throw new \Exception("$name is an unknown payment handler name");
    }

    /**
     * @return array
     */
    public function getCloudDNS(): array
    {
        return $this->cloudDNS;
    }

    /**
     * @param array $cloudDNS
     */
    public function setCloudDNS(array $cloudDNS): void
    {
        $this->cloudDNS = $cloudDNS;
    }
}