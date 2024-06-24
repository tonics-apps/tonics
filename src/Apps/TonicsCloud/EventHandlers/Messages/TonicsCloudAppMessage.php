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

use App\Apps\TonicsCloud\Services\AppService;
use App\Modules\Core\Events\OnAddMessageType;

class TonicsCloudAppMessage extends TonicsCloudMessageAbstract
{
    /**
     * @param object $event
     * @param $message
     *
     * @return mixed
     * @throws \Throwable
     */
    public function sendEvent (object $event, $message): mixed
    {
        if (!empty($message->app_id) &&
            !empty($message->id) &&
            !empty($message->container_id) &&
            !empty($message->eventType)) {

            if ($message->eventType === $event::EVENT_TYPE_DELETE) {
                $data = $this->dataTableData('id', $message->id, $message->id);
                return $event->sendEvent(OnAddMessageType::EVENT_TYPE_DELETE, $data);
            }

            if ($message->eventType === $event::EVENT_TYPE_UPDATE) {
                $data = AppService::RenderTableRow(AppService::DataTableHeaders(), AppService::GetContainerApp($message->app_id, $message->container_id));
                $data = $this->dataTableData('id', $message->id, $data);
                return $event->sendEvent(OnAddMessageType::EVENT_TYPE_UPDATE, $data);
            }
        }

        return false;
    }
}