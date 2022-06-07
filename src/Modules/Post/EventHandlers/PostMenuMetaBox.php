<?php

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
            $postData->getPostPaginationColumns(),
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