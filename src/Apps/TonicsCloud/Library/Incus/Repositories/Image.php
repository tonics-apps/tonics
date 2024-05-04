<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

class Image extends AbstractRepository
{

    /**
     * Adds a new image to the image store, here is an example importing image from a URL:
     *
     * ```
     * $client = new Client(new URL("https://xxx:xxxx"), $certAndKey);
     * $parameter = [
     *  'auto_update' => false,
     *  'aliases' => [ ['name' => 'AddImageAliasOrFingerPrint'] ],
     *  'source' => ['mode' => 'pull', 'type' => 'url', 'url' => "https://xxxx.com"]
     * ];
     * $response = $client->images()->add($parameter);
     * ```
     *
     * @throws \Exception
     */
    public function add(array $parameters): \stdClass|null
    {
        return $this->client->sendRequest($this->getEndPoint(), $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Get instances
     * @return \stdClass|null
     * @throws \Exception
     */
    public function all(): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . '?recursion=1', $this->client->getURL()::REQUEST_GET);
    }

    /**
     * @param string $fingerPrint
     * @return \stdClass
     * @throws \Exception
     */
    public function info(string $fingerPrint): \stdClass
    {
        return $this->client->sendRequest($this->getEndPoint($fingerPrint), $this->client->getURL()::REQUEST_GET);
    }

    /**
     * @param string $fingerPrint
     * @return \stdClass
     * @throws \Exception
     */
    public function delete(string $fingerPrint): \stdClass
    {
        return $this->client->sendRequest($this->getEndPoint($fingerPrint), $this->client->getURL()::REQUEST_DELETE);
    }

    /**
     * @param string $fingerPrint
     * @return string
     */
    protected function getEndPoint(string $fingerPrint = ''): string
    {
        $path = (!empty($fingerPrint)) ? "/images/$fingerPrint" : '/images';
        return $this->client->getURL()::getBaseURL() . $path;
    }
}