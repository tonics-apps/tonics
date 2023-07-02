<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class MediaMenus implements HandlerInterface
{
    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        \tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::MEDIA, ['mt_name' => 'Media','mt_url_slug' => '#0', 'mt_icon' => helper()->getIcon('play','icon:admin') ]);
            $tree->add(AdminMenuHelper::FILE_MANAGER, ['mt_name' => 'File Manager','mt_url_slug' => route('media.show'), 'mt_icon' => helper()->getIcon('media-file','icon:admin') ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_MEDIA])]);
    }
}