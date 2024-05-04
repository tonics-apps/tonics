<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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