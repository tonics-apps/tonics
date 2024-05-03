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

class Operation extends AbstractRepository
{

    /**
     * @throws \Exception
     */
    public function all(): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(false) . '?recursion=1', $this->client->getURL()::REQUEST_GET);
    }


    /**
     * @param string $uuid
     * @return \stdClass|null
     * @throws \Exception
     */
    public function info(string $uuid): ?\stdClass
    {
        $UUID = $this->extractUUID($uuid);
        if ($UUID){
            $url = $this->getEndPoint(false) . '/' . $UUID[0];
            return $this->client->sendRequest($url, $this->client->getURL()::REQUEST_GET);
        }

        return null;
    }

    /**
     * @param string $uuid
     * @return \stdClass|null
     * @throws \Exception
     */
    public function cancel(string $uuid): ?\stdClass
    {
        $UUID = $this->extractUUID($uuid);
        if ($UUID){
            $url = $this->getEndPoint(false) . '/' . $UUID[0];
            return $this->client->sendRequest($url, $this->client->getURL()::REQUEST_DELETE);
        }

        return null;
    }


    /**
     * Wait for operation to complete
     * @param string $uuid
     * @param int $timeout
     * Add -1 if you want to wait forever
     * @return \stdClass|null
     * @throws \Exception
     */
    public function wait(string $uuid, int $timeout = 300): ?\stdClass
    {
        $UUID = $this->extractUUID($uuid);
        if ($UUID){
            $url = $this->getEndPoint(false) . '/' . $UUID[0] . "/wait?timeout=$timeout";
            return $this->client->sendRequest($url, $this->client->getURL()::REQUEST_GET);
        }

        return null;
    }

    /**
     * @param $string
     * @return string[]|null
     */
    protected function extractUUID($string): array|null
    {
        $pattern = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i';
        preg_match_all($pattern, $string, $matches);
        // Extract the UUIDs from the matches, else
        if (!empty($matches[0])) {
            return $matches[0];
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getEndPoint($wss = true): string
    {
        if ($wss){
            return str_replace(['https', 'http'], 'wss', $this->client->getURL()::getBaseURL()). '/operations';
        }

        return $this->client->getURL()::getBaseURL(). '/operations';
    }
}