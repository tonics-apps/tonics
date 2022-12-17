<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\RequestInterceptor;

use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Post\Events\OnPostDefaultField;
use App\Modules\Post\Helper\PostRedirection;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

class CouponAccessView
{
    private CouponData $couponData;
    private array $coupon = [];
    private array $couponType = [];

    public function __construct(CouponData $couponData){
        $this->couponData = $couponData;
    }

    /**
     * @throws \Exception
     */
    public function handleCoupon(): void
    {
        $uniqueSlugID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $couponSlug = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
        $coupon = (array)$this->getCouponData()->getCouponUniqueID($uniqueSlugID);

        # if empty we can check with the coupon_slug and do a redirection
        if (empty($coupon)){
            $coupon = (array)$this->getCouponData()->getCouponUniqueID($couponSlug, 'coupon_slug');
            if (isset($coupon['slug_id'])){
                redirect(TonicsCouponActivator::getCouponAbsoluteURLPath($coupon), 302);
            }
        # if postSlug is not equals to $post['coupon_slug'], do a redirection to the correct one
        } elseif (isset($coupon['coupon_slug']) && $coupon['coupon_slug'] !== $couponSlug){
            redirect(TonicsCouponActivator::getCouponAbsoluteURLPath($coupon), 302);
        }

        if (key_exists('coupon_status', $coupon)) {
            if ($coupon['coupon_status'] === 1) {
                $this->coupon = $coupon; return;
            }

            ## Else, post is in draft or trash or in the future, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->coupon = $coupon; return;
            }
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }

    /**
     * @throws \Exception
     */
    public function handleCouponTypes(): void
    {
        $uniqueSlugID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $couponTypeSlug = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
        $couponTypeTable = TonicsCouponActivator::couponTypeTableName();
        $couponType = (array)db()->Select('*')->From($couponTypeTable)->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();

        # if empty we can check with the coupon_type_slug and do a redirection
        if (empty($couponType)){
            $couponType = (array)db()->Select('*')->From($couponTypeTable)->WhereEquals('coupon_type_slug', $uniqueSlugID)->FetchFirst();
            if (isset($couponType['slug_id'])){
                redirect(TonicsCouponActivator::getCouponTypeAbsoluteURLPath($couponType), 302);
            }
        # if catSlug is not equals to $category['cat_slug'], do a redirection to the correct one
        } elseif (isset($couponType['coupon_type_slug']) && $couponType['coupon_type_slug'] !== $couponTypeSlug){
            redirect(TonicsCouponActivator::getCouponTypeAbsoluteURLPath($couponType), 302);
        }


        if (key_exists('coupon_type_status', $couponType)) {
            $couponType['couponTypes'][] = array_reverse($this->couponData->getCouponTypesParent($couponType['coupon_type_parent_id'] ?? ''));
            $catCreatedAtTimeStamp = strtotime($couponType['created_at']);
            if ($couponType['coupon_type_status'] === 1 && time() >= $catCreatedAtTimeStamp) {
                $this->couponType = $couponType; return;
            }

            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->couponType = $couponType; return;
            }
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showPost(string $postView, $moreData = []): void
    {
        $coupon = $this->coupon;
        if (!empty($coupon)){
            $typeID = [];
            foreach ($coupon['couponTypes'] as $couponTypes){
                foreach ($couponTypes as $couponType){
                    $typeID[] = $couponType->coupon_type_id;
                }
            }

            $couponTableName = TonicsCouponActivator::couponTableName();
            $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
            $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();
            $userTable = Tables::getTable(Tables::USERS);

            $relatedCoupon = db()->Select(CouponData::getCouponTableJoiningRelatedColumns())
                ->From($couponToTypeTableName)
                ->Join($couponTableName, table()->pickTable($couponTableName, ['coupon_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_id']))
                ->Join($couponTypeTableName, table()->pickTable($couponTypeTableName, ['coupon_type_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_type_id']))
                ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($couponTableName, ['user_id']))
                ->addRawString("WHERE MATCH(coupon_name) AGAINST(?)")->addParam($coupon['coupon_name'])->setLastEmittedType('WHERE')
                ->WhereEquals('coupon_status', 1)
                ->WhereIn('coupon_type_id', $typeID)
                ->WhereNotIn('coupon_id', $coupon['coupon_id'])
                ->Where("$couponTableName.created_at", '<=', helper()->date())
                ->OrderByDesc(table()->pickTable($couponTableName, ['updated_at']))->SimplePaginate(6);

            $coupon['related_coupon'] = $relatedCoupon;
            $this->getCouponData()->unwrapForCoupon($coupon);

            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());
            event()->dispatch($this->getCouponData()->getOnCouponDefaultField());

            # We are only interested in the hidden slug
            $slugs = $this->getCouponData()->getOnCouponDefaultField()->getHiddenFieldSlug();
            # MoreData can't use the _fieldDetails here
            unset($moreData['_fieldDetails']);
            # Cache Post Data
            $onFieldUserForm->handleFrontEnd($slugs, [...$coupon, ...$moreData]);
            view($postView);
        }

        exit();
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showCouponType(string $postView, $moreData = []): void
    {
        $couponType = $this->couponType;
        if (!empty($couponType)){


            $couponTableName = TonicsCouponActivator::couponTableName();
            $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
            $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();

            $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();
            $couponRootPath = CouponSettingsController::getTonicsCouponRootPath();

            $couponData = [];
            try {
                $couponFieldSettings = $couponTableName . '.field_settings';
                $tblCol = table()->pickTableExcept($couponTableName,  ['updated_at'])
                    . ", CONCAT_WS('/', '/$couponRootPath', $couponTableName.slug_id, coupon_slug) as _preview_link "
                    . ", JSON_UNQUOTE(JSON_EXTRACT($couponFieldSettings, '$.seo_description')) as seo_description";

                $couponTypesIDResult = $this->getCouponData()->getChildCouponTypesOfParent($couponType['coupon_type_id']);
                $couponTypesID = [];
                foreach ($couponTypesIDResult as $couponTypeID){
                    $couponTypesID[] = $couponTypeID->coupon_type_id;
                }

                $couponData = db()->Select($tblCol)
                    ->From($couponToTypeTableName)
                    ->Join($couponTableName, table()->pickTable($couponTableName, ['coupon_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_id']))
                    ->Join($couponTypeTableName, table()->pickTable($couponTypeTableName, ['coupon_type_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_type_id']))
                    ->WhereEquals('coupon_status', 1)
                    ->WhereIn('coupon_type_id', $couponTypesID)
                    ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                        $db->WhereLike('coupon_name', url()->getParam('query'));
                    })
                    ->Where("$couponTableName.created_at", '<=', helper()->date())
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
                    })
                    ->SimplePaginate(AppConfig::getAppPaginationMax());

                $couponData = ['TonicsCouponData' => $couponData, 'TonicsCouponTypesData' => $couponTypesIDResult];

            } catch (\Exception $exception){
                // log..
            }

            $fieldSettings = json_decode($couponType['field_settings'], true);
            $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'coupon_type_content');
            $couponType = [...$fieldSettings, ...$couponType];

            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());

            event()->dispatch($this->getCouponData()->getOnCouponTypeDefaultField());
            $slugs = $this->getCouponData()->getOnCouponTypeDefaultField()->getHiddenFieldSlug();

            # MoreData can't use the _fieldDetails here
            unset($moreData['_fieldDetails']);
            $dataBundle = [...$couponType, ...$moreData, ...$couponData];
            $onFieldUserForm->handleFrontEnd($slugs, $dataBundle);
            view($postView, $dataBundle);
        }

        exit();
    }

    /**
     * @return CouponData
     */
    public function getCouponData(): CouponData
    {
        return $this->couponData;
    }

    /**
     * @param CouponData $couponData
     */
    public function setCouponData(CouponData $couponData): void
    {
        $this->couponData = $couponData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->getCouponData()->getFieldData();
    }
}