<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $lastMenuID = $event->getLastMenuID() + 15;
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_CORE, $event->userRole()), function ($event) use ($lastMenuID) {
            return $event->addMenu($lastMenuID, 'Cloud', helper()->getIcon('cloud'), route('tonicsCloud.instances.index'))

                ->addMenu($lastMenuID + 1, 'Images', helper()->getIcon('archive', 'icon:admin'), route('tonicsCloud.admin.images.create'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 2, 'New Image', helper()->getIcon('plus', 'icon:admin'), route('tonicsCloud.admin.images.create'), parent: $lastMenuID + 1)
                ->addMenu($lastMenuID + 3, 'All Images', helper()->getIcon('archive', 'icon:admin'), route('tonicsCloud.admin.images.index'), parent: $lastMenuID + 1);
        });

        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(TonicsCloudActivator::CAN_ACCESS_TONICS_CLOUD, $event->userRole()), function ($event) use ($lastMenuID) {
            return $event->addMenu($lastMenuID, 'Cloud', helper()->getIcon('cloud'), route('tonicsCloud.instances.index'))

                ->addMenu($lastMenuID + 4, 'Instances', helper()->getIcon('server', 'icon:admin'), route('tonicsCloud.instances.create'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 5, 'New Instance', helper()->getIcon('plus', 'icon:admin'), route('tonicsCloud.instances.create'), parent: $lastMenuID + 4)
                ->addMenu($lastMenuID + 6, 'All Instances', helper()->getIcon('server', 'icon:admin'), route('tonicsCloud.instances.index'), parent: $lastMenuID + 4)

                ->addMenu($lastMenuID + 7, 'Containers', helper()->getIcon('container', 'icon:admin'), route('tonicsCloud.containers.create'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 8, 'New Container', helper()->getIcon('plus', 'icon:admin'), route('tonicsCloud.containers.create'), parent: $lastMenuID + 7)
                ->addMenu($lastMenuID + 9, 'All Containers', helper()->getIcon('container', 'icon:admin'), route('tonicsCloud.containers.index'), parent: $lastMenuID + 7)

                ->addMenu($lastMenuID + 10, 'Apps', helper()->getIcon('app', 'icon:admin'), route('tonicsCloud.apps.index'), parent: $lastMenuID);
        });
    }
}