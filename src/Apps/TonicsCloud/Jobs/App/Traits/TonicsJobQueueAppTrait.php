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

namespace App\Apps\TonicsCloud\Jobs\App\Traits;

use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudAppMessage;
use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\Services\AppService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Events\OnAddMessageType;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

trait TonicsJobQueueAppTrait
{
    use TonicsJobQueueContainerTrait;

    /**
     * @return mixed|string
     */
    public function getAppID (): mixed
    {
        return $this->getDataAsArray()['app_id'] ?? '';
    }

    /**
     * @return CloudAppInterface
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function appObject (): CloudAppInterface
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
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateStatusMessage (string $statusMsg, $callableUpdateMore = null): void
    {
        $statusMsg = helper()->strLimit($statusMsg, 200);
        db(onGetDB: function (TonicsQuery $db) use ($callableUpdateMore, $statusMsg) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
            $db->Update($table)
                ->Set('app_status_msg', $statusMsg);
            if ($callableUpdateMore) {
                $callableUpdateMore($db);
            }
            $db->WhereEquals('fk_container_id', $this->getContainerID())
                ->WhereEquals('fk_app_id', $this->getAppID())
                ->Exec();

            $app = AppService::GetContainerApp($this->getAppID(), $this->getContainerID());
            message()->send(
                [
                    'id'           => $app->id,
                    'container_id' => $this->getContainerID(),
                    'app_id'       => $this->getAppID(),
                    'eventType'    => OnAddMessageType::EVENT_TYPE_UPDATE,
                ], TonicsCloudAppMessage::MessageTypeKey($this->getCustomerID()),
            );

        });
    }

    /**
     * @return string
     */
    public function getIncusContainerName (): string
    {
        return $this->getDataAsArray()['incus_container_name'] ?? '';
    }
}