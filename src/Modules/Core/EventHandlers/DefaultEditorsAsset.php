<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\EditorsAsset;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class DefaultEditorsAsset implements HandlerInterface
{
    public function handleEvent(object $event): void
    {
        /** @var $event EditorsAsset */
        $event
            ->addJS(AppConfig::getModuleAsset('Core', '/js/views/field/items/selection-manager/script-combined.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/tools/Widget/FeaturedImage.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/tools/Widget/FeaturedLink.js'));
    }
}