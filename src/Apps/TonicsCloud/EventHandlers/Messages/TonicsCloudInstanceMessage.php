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

namespace App\Apps\TonicsCloud\EventHandlers\Messages;

use App\Apps\TonicsCloud\Services\InstanceService;
use App\Modules\Core\Events\OnAddMessageType;

class TonicsCloudInstanceMessage extends TonicsCloudMessageAbstract
{

    /**
     * @param OnAddMessageType $event
     * @param $message
     *
     * @return mixed
     * @throws \Throwable
     */
    public function sendEvent (OnAddMessageType $event, $message): mixed
    {
        if (!empty($message->instance_id) && !empty($message->eventType)) {

            if ($message->eventType === $event::EVENT_TYPE_DELETE) {
                $data = $this->dataTableData('service_instance_id', $message->instance_id, $message->instance_id);
                return $event->sendEvent(OnAddMessageType::EVENT_TYPE_DELETE, $data);
            }

            if ($message->eventType === $event::EVENT_TYPE_UPDATE) {

                $instance = InstanceService::GetServiceInstances(
                    [
                        'instance_id' => $message->instance_id,
                        'column'      => 'service_instance_id',
                        'user_id'     => \session()::getUserID(),
                    ]);

                $data = InstanceService::RenderTableRow(InstanceService::DataTableHeaders(), $instance);
                $data = $this->dataTableData('service_instance_id', $message->instance_id, $data);
                return $event->sendEvent(OnAddMessageType::EVENT_TYPE_UPDATE, $data);
            }

        }

        return false;
    }
}