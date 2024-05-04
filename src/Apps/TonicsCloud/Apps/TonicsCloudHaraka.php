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