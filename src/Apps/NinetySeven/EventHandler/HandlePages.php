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

class HandlePages implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        addToGlobalVariable('Assets', ['css' => AppConfig::getAppAsset('NinetySeven', 'css/styles.min.css')]);

        /** @var $event BeforePageView */
        # Load Some Settings Option From Theme
        $fieldSettings = [...$event->getFieldSettings(), ...NinetySevenController::getSettingData()];
        $event->setFieldSettings($fieldSettings);

        switch ($event->getPagePath()){
            case '/'; $event->setViewName('Apps::NinetySeven/Views/Page/single'); break;
            case '/categories'; $event->setViewName('Apps::NinetySeven/Views/Page/category-page'); break;
            case '/posts'; $event->setViewName('Apps::NinetySeven/Views/Page/post-page'); break;
        }
    }
}