<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
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
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_CORE, $event->userRole()), function ($event) {

            return $event->addMenu(OnAdminMenu::SettingsMenuID, 'Settings', helper()->getIcon('cog', 'icon:admin'), route('general.settings'))
                ->addMenu(OnAdminMenu::SettingsMenuID + 1, 'Profile', helper()->getIcon('cog', 'icon:admin'), route('profile.settings'), parent: OnAdminMenu::SettingsMenuID)
                ->addMenu(OnAdminMenu::SettingsMenuID + 2, 'General', helper()->getIcon('cog', 'icon:admin'), route('general.settings'), parent: OnAdminMenu::SettingsMenuID)
                ->addMenu(OnAdminMenu::SettingsMenuID + 3, 'Logout', helper()->getIcon('sign-out', 'icon:admin'), '', parent: OnAdminMenu::SettingsMenuID)

                ->addMenu(
                    OnAdminMenu::ToolsMenuID,
                    'Tools',
                    helper()->getIcon('tools', 'icon:admin'),
                    '#0')
                ->addMenu(
                    OnAdminMenu::ExtensionMenuID,
                    'Extension',
                    helper()->getIcon('plugin', 'icon:admin'),
                    '#0', parent:  OnAdminMenu::ToolsMenuID)
                ->if(UserData::canAccess(Roles::CAN_ACCESS_THEME), function ($event) {
                    return $event->addMenu(OnAdminMenu::ThemesMenuID, 'Themes', helper()->getIcon('theme', 'icon:admin'), route('themes.index'), parent:  OnAdminMenu::ExtensionMenuID);
                })
                ->if(UserData::canAccess(Roles::CAN_ACCESS_PLUGIN), function ($event) {
                    return $event->addMenu(OnAdminMenu::PluginMenuID, 'Plugins', helper()->getIcon('plugin', 'icon:admin'), route('plugins.index'), parent:  OnAdminMenu::ExtensionMenuID);
                })

                ->if(UserData::canAccess(Roles::CAN_ACCESS_TRACK), function ($event) {
                    return $event->addMenu(OnAdminMenu::LicenseMenuID, 'License', helper()->getIcon('license','icon:admin'), route('licenses.create'), parent:  OnAdminMenu::TrackMenuID)
                        ->addMenu(OnAdminMenu::LicenseMenuID + 1, 'New License', helper()->getIcon('plus', 'icon:admin'), route('licenses.create'), parent: OnAdminMenu::LicenseMenuID)
                        ->addMenu(OnAdminMenu::LicenseMenuID + 2, 'All License', helper()->getIcon('notes', 'icon:admin'), route('licenses.index'), parent: OnAdminMenu::LicenseMenuID);
                })->addMenu(OnAdminMenu::ImportsMenuID, 'Imports', helper()->getIcon('upload', 'icon:admin'), route('imports.index'), parent:  OnAdminMenu::ToolsMenuID);
                //->addMenu(OnAdminMenu::LicenseMenuID + 4, 'Exports', helper()->getIcon('download', 'icon:admin'), route('imports.export'), parent:  OnAdminMenu::ToolsMenuID);
        });

        $event->if(UserData::canAccess(Roles::CAN_ACCESS_CUSTOMER), function ($event) {
            return $event->addMenu(OnAdminMenu::DashboardMenuID, 'Dashboard', helper()->getIcon('dashboard', 'icon:admin'), route('customer.dashboard'))
                ->addMenu(OnAdminMenu::SettingsMenuID, 'Settings', helper()->getIcon('cog', 'icon:admin'), route('customer.settings'))
                ->addMenu(OnAdminMenu::DashboardMenuID + 1, 'Profile', helper()->getIcon('cog', 'icon:admin'), route('customer.settings'), parent: OnAdminMenu::SettingsMenuID);
        });
    }
}
