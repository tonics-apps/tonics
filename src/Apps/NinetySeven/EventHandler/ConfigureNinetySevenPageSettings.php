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

namespace App\Apps\NinetySeven\EventHandler;

use App\Apps\NinetySeven\Controller\NinetySevenController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Page\Events\BeforePageView;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class ConfigureNinetySevenPageSettings implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event BeforePageView */
        # Load Some Settings Option From Theme
        $ninetySevenSettings = NinetySevenController::getSettingData();
        unset($ninetySevenSettings['_fieldDetails']);
        $fieldSettings = [...$event->getFieldSettings(), ...$ninetySevenSettings];
        $event->setFieldSettings($fieldSettings);
    }
}