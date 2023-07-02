<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class FieldMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {
        \tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::FIELD, ['mt_name' => 'Field','mt_url_slug' => route('fields.index'), 'mt_icon' => helper()->getIcon('widget','icon:admin') ]);
            $tree->add(AdminMenuHelper::FIELD_NEW, ['mt_name' => 'New Field','mt_url_slug' => route('fields.create'), 'mt_icon' => helper()->getIcon('plus','icon:admin') ]);

          //  AdminMenuHelper::addToRouteMapper(route('fields.index'), AdminMenuHelper::FIELD);
           // AdminMenuHelper::addToRouteMapper(route('fields.create'), AdminMenuHelper::FIELD_NEW);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_FIELD])]);
    }
}