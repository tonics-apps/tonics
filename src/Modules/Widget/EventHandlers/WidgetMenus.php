<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\EventHandlers;

use App\Modules\Core\Library\AdminMenuPaths;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class WidgetMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {

        tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuPaths::WIDGET, [
                'mt_name' => 'Widget',
                'mt_url_slug' => route('widgets.index'),
                'mt_icon' => helper()->getIcon('widget', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::WIDGET_NEW, [
                'mt_name' => 'Widget',
                'mt_url_slug' => route('widgets.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_WIDGET])]);
    }
}