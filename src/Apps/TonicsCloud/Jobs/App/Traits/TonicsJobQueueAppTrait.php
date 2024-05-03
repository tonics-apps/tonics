<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\App\Traits;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

trait TonicsJobQueueAppTrait
{
    /**
     * @return mixed|string
     */
    public function getContainerID(): mixed
    {
        return $this->getDataAsArray()['container_id'] ?? '';
    }

    /**
     * @return mixed|string
     */
    public function getAppID(): mixed
    {
        return $this->getDataAsArray()['app_id'] ?? '';
    }

    /**
     * @return CloudAppInterface
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function appObject(): CloudAppInterface
    {
        $class = $this->getDataAsArray()['app_class'] ?? '';
        $postFlightData = $this->getDataAsArray()['postFlight'] ?? '';
        /** @var CloudAppInterface $obj */
        $obj = container()->get($class);
        $obj->setContainerID($this->getContainerID())->setPostPrepareForFlight($postFlightData)->setIncusContainerName($this->getIncusContainerName());
        return $obj;
    }

    /**
     * @param string $statusMsg
     * @param null $callableUpdateMore -- set more data, you'll be passed the TonicsQuery
     * @return void
     * @throws \Exception
     */
    public function updateStatusMessage(string $statusMsg, $callableUpdateMore = null): void
    {
        $statusMsg = helper()->strLimit($statusMsg, 200);
        db(onGetDB: function (TonicsQuery $db) use ($callableUpdateMore, $statusMsg) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
            $db->Update($table)
                ->Set('app_status_msg', $statusMsg);
            if ($callableUpdateMore){
                $callableUpdateMore($db);
            }
            $db->WhereEquals('fk_container_id', $this->getContainerID())
                ->WhereEquals('fk_app_id', $this->getAppID())
                ->Exec();
        });
    }

    /**
     * @return string
     */
    public function getIncusContainerName(): string
    {
        return $this->getDataAsArray()['incus_container_name'] ?? '';
    }
}