<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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