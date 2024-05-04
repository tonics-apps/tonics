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
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();


        $couponData = null;
        db(onGetDB: function ($db) use ($couponTypeTableName, &$couponData){

            $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();
            $userTable = Tables::getTable(Tables::USERS);
            $couponTableName = TonicsCouponActivator::couponTableName();

            $couponData = $db->Select(CouponData::getCouponTableJoiningRelatedColumns())
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
                    $sort_by = 'started_at';
                    if (url()->getParam('sort_by') === '1'){
                        $sort_by = 'expired_at';
                    }
                    if (url()->getParam('sort_by') === '2'){
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
        });

        $couponTypes = null;
        db(onGetDB: function ($db) use ($couponTypeTableName, &$couponTypes){
            $couponTypes = $db->Select(table()->pickTableExcept($couponTypeTableName, ['field_settings', 'created_at', 'updated_at']))
                ->From($couponTypeTableName)->FetchResult();
        });

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