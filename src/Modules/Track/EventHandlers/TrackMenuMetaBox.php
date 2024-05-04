<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Track\EventHandlers;

use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackMenuMetaBox implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $trackData = new TrackData();
        $paginationInfo = $trackData->generatePaginationData(
            $trackData->getTrackPaginationColumns(),
            'track_title',
            $trackData->getTrackTable());

        /** @var OnMenuMetaBox $event */
        $event->addMenuBox('Tracks', '', $paginationInfo,
            function () use ($paginationInfo, $event) {
                return $event->moreMenuItems('Tracks', $paginationInfo);
            }, dataCondition: function ($data){
                if (isset($data->track_status) && $data->track_status < 0){
                    return false;
                }
                return true;
            });
    }
}