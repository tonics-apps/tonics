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

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DeactivateCombiningFilesInProduction implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event BeforeCombineModeOperation */
        if (AppConfig::isProduction()) {
            $event->setCombineFiles(false);

            # REPLACE .js with .min.js if available
            $file = $event->getRootPath() . DIRECTORY_SEPARATOR . trim($event->getOutputFile(), '/\\');
            $minJs = str_replace('.js', '.min.js', $file);
            if (file_exists($minJs)){
                $event->setOutputFile(str_replace('.js', '.min.js', $event->getOutputFile()));
            }
        }

    }
}