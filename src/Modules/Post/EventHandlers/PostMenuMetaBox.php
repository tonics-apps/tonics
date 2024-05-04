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

namespace App\Modules\Post\EventHandlers;

use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostMenuMetaBox implements HandlerInterface
{


    /**
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $postData = new PostData();
        $paginationInfo = $postData->generatePaginationData(
            $postData::getPostPaginationColumns(),
            'post_title',
            $postData->getPostTable());

        /** @var OnMenuMetaBox $event */
        $event->addMenuBox('Posts', helper()->getIcon('note'), $paginationInfo,
            function () use ($paginationInfo, $event) {
                return $event->moreMenuItems('Posts', $paginationInfo);
            }, dataCondition: function ($data){
                if (isset($data->post_status) && $data->post_status < 0){
                    return false;
                }
                return true;
            });
    }
}