<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * This Listens to the OnAdminMenu and Whenever the event fires, we call this listener
 *
 * The purpose of this listener is to add core menu functionality, such as Dashboard, Settings, and anything related to core
 * Class DefaultTemplate
 * @package Modules\Core\EventHandlers
 */
class CoreMenus implements HandlerInterface
{

    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_CORE), $event->userRole()), function ($event) {

            return $event->addMenu(
                    OnAdminMenu::ToolsMenuID,
                    'Tools',
                    helper()->getIcon('tools', 'icon:admin'),
                    '#0')
                ->addMenu( OnAdminMenu::AppsMenuID, 'Apps', helper()->getIcon('plugin', 'icon:admin'), route('apps.index'), parent:  OnAdminMenu::ToolsMenuID)
                    ->addMenu(OnAdminMenu::AppsMenuID + 1, 'Force Update Check', helper()->getIcon('more-horizontal', 'icon:admin'), route('apps.discover_updates'), parent:  OnAdminMenu::AppsMenuID)
                    ->addMenu(OnAdminMenu::AppsMenuID + 2, 'Upload App', helper()->getIcon('upload', 'icon:admin'), route('apps.uploadForm'), parent:  OnAdminMenu::AppsMenuID)

                ->addMenu( OnAdminMenu::JobsMenuID, 'Job Manager', helper()->getIcon('jobs', 'icon:admin'), route('jobs.jobsIndex'), parent:  OnAdminMenu::ToolsMenuID)
                ->addMenu(OnAdminMenu::JobsMenuID + 1, 'Jobs', helper()->getIcon('jobs', 'icon:admin'), route('jobs.jobsIndex'), parent:  OnAdminMenu::JobsMenuID)
                ->addMenu(OnAdminMenu::JobsMenuID + 2, 'Jobs Scheduler', helper()->getIcon('schedule', 'icon:admin'), route('jobs.jobsSchedulerIndex'), parent:  OnAdminMenu::JobsMenuID)

                ->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_TRACK), $event->userRole()), function ($event) {
                    return $event->addMenu(OnAdminMenu::LicenseMenuID, 'License', helper()->getIcon('license','icon:admin'), route('licenses.create'), parent:  OnAdminMenu::TrackMenuID)
                        ->addMenu(OnAdminMenu::LicenseMenuID + 1, 'New License', helper()->getIcon('plus', 'icon:admin'), route('licenses.create'), parent: OnAdminMenu::LicenseMenuID)
                        ->addMenu(OnAdminMenu::LicenseMenuID + 2, 'All License', helper()->getIcon('notes', 'icon:admin'), route('licenses.index'), parent: OnAdminMenu::LicenseMenuID);
                })->addMenu(OnAdminMenu::ImportsMenuID, 'Imports', helper()->getIcon('upload', 'icon:admin'), route('imports.index'), parent:  OnAdminMenu::ToolsMenuID);
                //->addMenu(OnAdminMenu::LicenseMenuID + 4, 'Exports', helper()->getIcon('download', 'icon:admin'), route('imports.export'), parent:  OnAdminMenu::ToolsMenuID);
        });

        $event->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_CUSTOMER), $event->userRole()), function ($event) {
            return $event
                ->addMenu(OnAdminMenu::DashboardMenuID, 'Orders', helper()->getIcon('cart', 'icon:admin'), route('customer.order.index'));
        });
    }
}
