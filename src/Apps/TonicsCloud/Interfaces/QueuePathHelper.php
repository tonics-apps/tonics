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

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\TonicsCloudActivator;

final class QueuePathHelper
{
    /**
     * @param $serviceInstance
     * @param string $path
     * @return array
     * @throws \Throwable
     */
    public static function InstancePath($serviceInstance, string $path, callable $handlerName = null): array
    {
        if (is_null($serviceInstance) || !isset($serviceInstance->others)) {
            return DefaultJobQueuePaths::$path();
        }

        $instanceOthers = json_decode($serviceInstance->others);
        if (isset($instanceOthers->serverHandlerName)) {
            $handler = TonicsCloudActivator::getCloudServerHandler($instanceOthers->serverHandlerName);
            if ($handlerName) {
                $handlerName($instanceOthers->serverHandlerName);
            }
            return $handler::$path();
        }

        return DefaultJobQueuePaths::$path();
    }

}