<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\EventHandlers\PageTemplates;

use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsCouponDefaultPageTemplate implements PageTemplateInterface, HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'TonicsCoupon_DefaultPageTemplate';
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $pageTemplate->setViewName('Apps::TonicsCoupon/Views/FrontPage/coupon-page');
        $fieldSettings = $pageTemplate->getFieldSettings();
        $couponTableName = TonicsCouponActivator::couponTableName();
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
        $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();
        $userTable = Tables::getTable(Tables::USERS);

        $couponData = db()->Select(CouponData::getCouponTableJoiningRelatedColumns())
            ->From($couponToTypeTableName)
            ->Join($couponTableName, table()->pickTable($couponTableName, ['coupon_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_id']))
            ->Join($couponTypeTableName, table()->pickTable($couponTypeTableName, ['coupon_type_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_type_id']))
            ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($couponTableName, ['user_id']))
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('coupon_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('coupon_status', 1);

                })
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('coupon_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('cat'), function (TonicsQuery $db) {
                $db->WhereIn('coupon_type_id', url()->getParam('cat'));

            })->when(url()->hasParamAndValue('coupon_type'), function (TonicsQuery $db) {
                $db->WhereIn('coupon_type_id', url()->getParam('coupon_type'));
            })
            ->GroupBy('coupon_id')
            ->when(url()->hasParamAndValue('order_by'), function (TonicsQuery $db) use ($couponTableName) {
                $orderIsDesc = true;
                if (url()->getParam('order_by') === '1'){
                    $orderIsDesc = false;
                }
                $sort_by = 'expired_at';
                if (url()->getParam('sort_by') === '1'){
                    $sort_by = 'created_at';
                }
                if ($orderIsDesc){
                    $db->OrderByDesc(table()->pickTable($couponTableName, [$sort_by]));
                } else {
                    $db->orderByAsc(table()->pickTable($couponTableName, [$sort_by]));
                }
            }, function (TonicsQuery $db) use ($couponTableName) {
                $db->orderByAsc(table()->pickTable($couponTableName, ['expired_at']));
            })->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        $couponTypes = db()->Select(table()->pickTableExcept($couponTypeTableName, ['field_settings', 'created_at', 'updated_at']))
            ->From($couponTypeTableName)->FetchResult();
        $fieldSettings['TonicsCouponData'] = $couponData;
        $fieldSettings['TonicsCouponTypeData'] = (new CouponData())->couponTypeCheckBoxListing($couponTypes, url()->getParam('coupon_type') ?? [], type: 'checkbox');
        addToGlobalVariable('Assets', ['css' => [AppConfig::getAppAsset('TonicsCoupon', 'css/styles.min.css')]]);
        $pageTemplate->setFieldSettings($fieldSettings);
    }

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }
}