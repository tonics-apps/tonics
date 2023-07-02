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
use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

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
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {
        \tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::DASHBOARD, ['mt_name' => 'Dashboard', 'mt_url_slug' => route('admin.dashboard'), 'ignore' => true, 'home' => true]);

            $tree->add(AdminMenuHelper::TOOL, ['mt_name' => 'Tools', 'mt_url_slug' => '#0', 'mt_icon' => helper()->getIcon('tools', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APPS, ['mt_name' => 'Apps','mt_url_slug' => route('apps.index'),'mt_icon' => helper()->getIcon('plugin', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APP_FORCE_UPDATE_CHECK, ['mt_name' => 'Force Update Check','mt_url_slug' => route('apps.discover_updates'),'mt_icon' => helper()->getIcon('more-horizontal', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APP_UPLOAD_APP, ['mt_name' => 'Upload App','mt_url_slug' => route('apps.uploadForm'),'mt_icon' => helper()->getIcon('upload', 'icon:admin')]);

            $tree->add(AdminMenuHelper::JOB_MANAGER, ['mt_name' => 'Job Manager','mt_url_slug' => route('jobs.jobsIndex'),'mt_icon' => helper()->getIcon('jobs', 'icon:admin')]);

            $tree->add(AdminMenuHelper::JOBS, ['mt_name' => 'Jobs','mt_url_slug' => route('jobs.jobsIndex'),'mt_icon' => helper()->getIcon('jobs', 'icon:admin')]);

            $tree->add(AdminMenuHelper::JOB_SCHEDULER, ['mt_name' => 'Jobs Scheduler','mt_url_slug' => route('jobs.jobsSchedulerIndex'), 'mt_icon' => helper()->getIcon('schedule', 'icon:admin')]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CORE])] );

        \tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::CUSTOMER_ORDERS, ['mt_name' => 'Orders','mt_url_slug' => route('customer.order.index'), 'mt_icon' => helper()->getIcon('cart','icon:admin') ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CUSTOMER])], AdminMenuHelper::PRIORITY_EXTREME);
    }
}
