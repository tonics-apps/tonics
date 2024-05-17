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

namespace App\Apps\TonicsCloud\Interfaces;

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