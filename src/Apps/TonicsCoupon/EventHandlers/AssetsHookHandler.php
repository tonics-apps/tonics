<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\EventHandlers;

use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class AssetsHookHandler implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('in_head_stylesheet', function (TonicsView $tonicsView){
            $foundURL = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode();
            $page = $foundURL->getMoreSettings('GET');
            $tonicCouponTemplates = [
                'TonicsCoupon_DefaultPageTemplate' => 'TonicsCoupon_DefaultPageTemplate',
            ];

            $couponRootPath = CouponSettingsController::getTonicsCouponRootPath();
            $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();

            $css = AppConfig::getAppAsset('TonicsCoupon', 'css/styles.min.css');
            $css = "<link rel='stylesheet' type='text/css' href='$css'>" . "\n";
            if (isset($page->page_template) && isset($tonicCouponTemplates[$page->page_template])){
                return $css;
            }

            if (isset($foundURL->getSettings()['GET']['url'][1])){
                $rootName = $foundURL->getSettings()['GET']['url'][1];
                if ($rootName === $couponRootPath || $rootName === $couponTypeRootPath){
                    return $css;
                }
            }

            return '';
        });

    }
}