<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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