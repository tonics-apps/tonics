<?php

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Post\Events\OnPostCategoryCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use JetBrains\PhpStorm\NoReturn;

class HandleNewPostCategoryRedirection implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    #[NoReturn] public function handleEvent(object $event): void
    {
        /**
         * @var OnPostCategoryCreate $event
         */
        session()->flash(['Post Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.edit', ['category' => $event->getCatSlug()]));
    }
}