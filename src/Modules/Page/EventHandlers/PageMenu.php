<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class PageMenu implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception|\Throwable
     */
    public function handleEvent(object $event): void
    {

        tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::PAGE, [
                'mt_name' => 'Pages',
                'mt_url_slug' => route('pages.index'),
                'mt_icon' => helper()->getIcon('archive', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::PAGE_NEW, [
                'mt_name' => 'New Page',
                'mt_url_slug' => route('pages.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);
        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_PAGE])], AdminMenuHelper::PRIORITY_EXTREME);

    }
}