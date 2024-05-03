<?php
/*
 * Copyright (c) 2024. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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