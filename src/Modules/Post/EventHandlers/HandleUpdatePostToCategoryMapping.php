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
        /**
         * @var OnPostUpdate $event
         */

        $toInsert = [];
        foreach ($event->getPostCatIDS() as $catID){
            $toInsert[] = [
                'fk_cat_id' => $catID,
                'fk_post_id' => $event->getPostID(),
            ];
        }

        $table = $event->getPostData()->getPostToCategoryTable();
        db()->FastDelete($table, db()->WhereIn('fk_post_id', $event->getPostID()));
        db()->Insert($table, $toInsert);
    }
}