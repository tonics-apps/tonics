<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Apps;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;

class TonicsCloudHaraka extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * @inheritDoc
     */
    public function updateSettings(): mixed
    {
        // TODO: Implement settings() method.
    }

    /**
     * @inheritDoc
     */
    public function install(): mixed
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): mixed
    {
        // TODO: Implement uninstall() method.
    }

    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): mixed
    {

    }

    public function reload()
    {
        return;
    }

    public function stop()
    {
        return;
    }

    public function start()
    {
        return;
    }

    public function isStatus(string $statusString): bool
    {
        return true;
    }
}