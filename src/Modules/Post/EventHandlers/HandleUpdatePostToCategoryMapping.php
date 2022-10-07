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

use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Post\Events\OnPostUpdate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleUpdatePostToCategoryMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        dd($event);
        /**
         * @var OnPostUpdate $event
         */
        $postToCategoryUpdate = [
            'fk_cat_id' => $event->getPostCatIDS(),
            'fk_post_id' => $event->getPostID(),
        ];

        dd($postToCategoryUpdate);

        db()->FastUpdate($event->getPostData()->getPostToCategoryTable(), $postToCategoryUpdate, db()->Where('fk_post_id', '=', $event->getPostID()));
    }
}