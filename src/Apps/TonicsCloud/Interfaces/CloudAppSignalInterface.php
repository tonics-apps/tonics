<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Library\Incus\Client;

interface CloudAppSignalInterface
{
    const STATUS_STOPPED = 'STATUS_STOPPED';
    const STATUS_RUNNING = 'STATUS_RUNNING';

    const SystemDSignalStop = 'stop';
    const SystemDSignalStart = 'start';
    const SystemDSignalRestart = 'restart';
    const SystemDSignalReload = 'reload';

    /**
     * Reload App In The Container, a Reload is not supposed to abruptly kill the app event if the app can't be reloaded, so, handle it properly
     */
    public function reload();

    /**
     * Stop App In The Container
     */
    public function stop();

    /**
     * Start App In The Container
     */
    public function start();

    /**
     * Check if instance status is equal to $statusString
     * @param string $statusString
     * To promote, interoperability, let this be any of CloudServerInterface::STATUS_STOPPED, CloudServerInterface::STATUS_RUNNING, or any
     * from CloudServerInterface
     * @return bool
     */
    public function isStatus(string $statusString): bool;
}