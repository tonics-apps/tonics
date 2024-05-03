<?php
/*
 * Copyright (c) 2024. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Library\Incus\Repositories;

use App\Apps\TonicsCloud\Library\Incus\Interface\AbstractRepository;

class Server extends AbstractRepository
{

    /**
     * Shows the full server environment and configuration.
     *
     * @throws \Exception
     */
    public function environment()
    {
        return $this->client->sendRequest($this->getEndPoint(), $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Gets the hardware information profile of the server.
     * @throws \Exception
     */
    public function resources()
    {
        return $this->client->sendRequest($this->getEndPoint() . '/resources', $this->client->getURL()::REQUEST_GET);
    }

    /**
     * @inheritDoc
     */
    protected function getEndPoint(): string
    {
        return $this->client->getURL()::getBaseURL();
    }
}