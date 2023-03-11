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
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewPostSlugIDGeneration implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in an unlikely case that a collision occur, we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception $exception){
                // Log..
                // Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnPostCreate $event
         */
        $slugGen = helper()->generateUniqueSlugID($event->getPostID());
        $postToUpdate = $event->getPostData()->createPost(['post_slug'], false);
        $postToUpdate['slug_id'] = $slugGen;
        db(onGetDB: function ($db) use ($postToUpdate, $event){
            $db->FastUpdate($event->getPostData()->getPostTable(), $postToUpdate, db()->Where('post_id', '=', $event->getPostID()));
        });

        $event->getPost()->slug_id = $slugGen;
    }
}