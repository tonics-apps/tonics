<?php

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
            'fk_cat_id' => input()->fromPost()->retrieve('fk_cat_id', ''),
            'fk_post_id' => $event->getPostID(),
        ];

        $event->getPostData()->insertForPost(
            $postToCategory,
            PostData::PostCategory_INT,
            $event->getPostData()->getPostToCategoriesColumns());
    }
}