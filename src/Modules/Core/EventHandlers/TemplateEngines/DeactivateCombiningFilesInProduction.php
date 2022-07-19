<?php

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
        }
    }
}