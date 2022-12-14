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
use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Page\Events\BeforePageView;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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
        $ninetySevenSettings = NinetySevenController::getSettingData();
        unset($ninetySevenSettings['_fieldDetails']);
        $fieldSettings = [...$event->getFieldSettings(), ...$ninetySevenSettings];
        $event->setFieldSettings($fieldSettings);

        switch ($event->getPagePath()){
            case '/'; $this->handleHomePage($event); break;
            case '/categories'; $event->setViewName('Apps::NinetySeven/Views/Page/category-page'); break;
            case '/posts'; $event->setViewName('Apps::NinetySeven/Views/Page/post-page'); break;
            case '/coupons'; $this->handleCouponPage($event);
        }
    }

    /**
     * @throws \Exception
     */
    private function handleHomePage(BeforePageView $event)
    {
        $ninetySevenHomePage = [
            'Featured' => [],
            'Best' => [],
            'OtherCategories' => [],
        ];

        $featuredAndTopBestParent = 'ninetySeven_featured_and_top_best_modular';
        $featuredLabelInputName = 'ninetySeven_featured_label';
        $featuredPostQuery = 'ninetySeven_featured_postQuery';
        $bestLabelInputName = 'ninetySeven_best_label';
        $bestPostQueryLabel = 'ninetySeven_best_postQuery';

        # Other Post Category...

        $otherPostCategoryParent = 'ninetySeven_other_post_category_repeater';
        $otherPostCategoryTitle = 'ninetySeven_category_title';
        $otherPostCategoryLink = 'ninetySeven_category_link';
        $otherPostCategoryDesc = 'ninetySeven_category_description';
        $otherPostCategoryQuery = 'ninetySeven_categoryQuery';

        $event->setViewName('Apps::NinetySeven/Views/Page/single');

        if (isset($event->getFieldSettings()['_fieldDetails']) && is_array($fieldDetails = json_decode($event->getFieldSettings()['_fieldDetails']))){
            $fieldDetails = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field){
                if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                    $fieldOption = json_decode($field->field_options);
                    $field->field_options = $fieldOption;
                }
                return $field;
            });

            foreach ($fieldDetails as $field){
                if (isset($field->field_options)){
                    if ($field->field_input_name === $featuredAndTopBestParent && isset($field->_children)){
                        foreach ($field->_children as $child){

                            if ($child->field_input_name === $featuredLabelInputName){
                                $ninetySevenHomePage['Featured']['Label'] = $child->field_options->ninetySeven_featured_label;
                            }
                            if ($child->field_input_name === $featuredPostQuery){
                                if (isset($child->_children[0]->_children)){
                                    $ninetySevenHomePage['Featured']['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                }
                            }
                            if ($child->field_input_name === $bestLabelInputName){
                                $ninetySevenHomePage['Best']['Label'] = $child->field_options->ninetySeven_best_label;
                            }
                            if ($child->field_input_name === $bestPostQueryLabel){
                                if (isset($child->_children[0]->_children)){
                                    try {
                                        $ninetySevenHomePage['Best']['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                    }catch (\Throwable $throwable){
                                        // Log...
                                        $ninetySevenHomePage['Best']['QueryData'] = null;
                                    }
                                }
                            }
                        }

                    }

                    # Other Post Category...
                    if ($field->field_input_name === $otherPostCategoryParent){
                        if (isset($field->_children)){
                            $currentOther = [];
                            foreach ($field->_children as $child){

                                if ($child->field_input_name === $otherPostCategoryTitle){
                                    $currentOther['Title'] = $child->field_options->ninetySeven_category_title ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryLink){
                                    $currentOther['Link'] = $child->field_options->ninetySeven_category_link ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryDesc){
                                    $currentOther['Desc'] = $child->field_options->ninetySeven_category_description ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryQuery){
                                    if (isset($child->_children[0]->_children)){
                                        try {
                                            $currentOther['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                        }catch (\Throwable $throwable){
                                            // Log...
                                            $currentOther['QueryData'] = null;
                                        }
                                    }
                                    $ninetySevenHomePage['OtherCategories'][] = $currentOther;
                                }
                            }
                        }
                    }
                }
            }
        }

        $fieldSettings = $event->getFieldSettings();
        $fieldSettings['NinetySevenHomePage'] = $ninetySevenHomePage;
        $event->setFieldSettings($fieldSettings);
        $ninetySevenHomePage = null;
    }

    /**
     * @throws \Exception
     */
    private function handleCouponPage(BeforePageView $event)
    {
        $event->setViewName('Apps::NinetySeven/Views/Page/coupon-page');
        $fieldSettings = $event->getFieldSettings();
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

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('coupon_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('cat'), function (TonicsQuery $db) {
                $db->WhereIn('coupon_type_id', url()->getParam('cat'));

            })
            ->GroupBy('coupon_id')
            ->OrderByDesc(table()->pickTable($couponTableName, ['expired_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        // dd($couponData);

        $fieldSettings['TonicsCouponData'] = $couponData;
        $event->setFieldSettings($fieldSettings);
    }
}