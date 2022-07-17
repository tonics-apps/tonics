<?php

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
        $event->getPostData()->updateWithCondition($postToUpdate, ['cat_id' => $event->getCatID()], $event->getPostData()->getCategoryTable());
        $event->getCategory()->slug_id = $slugGen;
    }
}