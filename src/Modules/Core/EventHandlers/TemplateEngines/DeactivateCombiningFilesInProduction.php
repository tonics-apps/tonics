<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DeactivateCombiningFilesInProduction implements HandlerInterface
{
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