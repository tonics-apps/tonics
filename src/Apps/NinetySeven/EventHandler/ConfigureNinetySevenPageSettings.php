<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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