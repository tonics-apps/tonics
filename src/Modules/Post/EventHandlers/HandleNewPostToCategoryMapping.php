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

use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewPostToCategoryMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {

        /**
         * @var OnPostCreate $event
         */
        $postToCategory = [
            'fk_cat_id' => $event->getPostCatID(),
            'fk_post_id' => $event->getPostID(),
        ];

        $event->getPostData()->insertForPost(
            $postToCategory,
            PostData::PostCategory_INT,
            $event->getPostData()->getPostToCategoriesColumns());
    }
}