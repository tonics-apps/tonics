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

use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewCategorySlugIDGeneration implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in a unlikely case that a collision occur, we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception){
                // log.. Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnPostCategoryCreate $event
         */
        $slugGen = $event->getCatID() . random_int(PHP_INT_MIN, PHP_INT_MAX). hrtime(true);
        $slugGen = hash('xxh3', $slugGen);
        $postToUpdate = $event->getPostData()->createCategory(['cat_slug']);
        $postToUpdate['slug_id'] = $slugGen;
        db()->FastUpdate($event->getPostData()->getCategoryTable(), $postToUpdate, db()->Where('cat_id', '=', $event->getCatID()));
        $event->getCategory()->slug_id = $slugGen;
    }
}