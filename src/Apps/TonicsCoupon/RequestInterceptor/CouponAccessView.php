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
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostDefaultField;
use App\Modules\Post\Helper\PostRedirection;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

class CouponAccessView
{
    private CouponData $couponData;
    private array $coupon = [];
    private array $category = [];

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
    public function handleCategory(): void
    {
        $uniqueSlugID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $catSlug = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
        $category = (array)$this->getCouponData()->selectWithConditionFromCategory(['*'], "slug_id = ?", [$uniqueSlugID]);

        # if empty we can check with the cat_slug and do a redirection
        if (empty($category)){
            $category = (array)$this->getCouponData()->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$catSlug]);
            if (isset($category['slug_id'])){
                redirect(PostRedirection::getCategoryAbsoluteURLPath($category), 302);
            }
        # if catSlug is not equals to $category['cat_slug'], do a redirection to the correct one
        } elseif (isset($category['cat_slug']) && $category['cat_slug'] !== $catSlug){
            redirect(PostRedirection::getCategoryAbsoluteURLPath($category), 302);
        }


        if (key_exists('cat_status', $category)) {
            $category['categories'][] = array_reverse($this->couponData->getPostCategoryParents($category['cat_parent_id'] ?? ''));
            $catCreatedAtTimeStamp = strtotime($category['created_at']);
            if ($category['cat_status'] === 1 && time() >= $catCreatedAtTimeStamp) {
                $this->category = $category; return;
            }

            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->category = $category; return;
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
    #[NoReturn] public function showCategory(string $postView, $moreData = []): void
    {
        $category = $this->category;
        if (!empty($category)){

            # GET CORRESPONDING POST IN CATEGORY
            $postTbl = Tables::getTable(Tables::POSTS);
            $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
            $CatTbl = Tables::getTable(Tables::CATEGORIES);

            $postData = [];
            try {
                $postFieldSettings = $postTbl . '.field_settings';
                $tblCol = table()->pickTableExcept($postTbl,  ['updated_at'])
                    . ", CONCAT_WS('/', '/posts', $postTbl.slug_id, post_slug) as _preview_link "
                    . ", JSON_UNQUOTE(JSON_EXTRACT($postFieldSettings, '$.seo_description')) as post_description";

                $catIDSResult = $this->getCouponData()->getChildCategoriesOfParent($category['cat_id']);
                $catIDS = [];
                foreach ($catIDSResult as $catID){
                    $catIDS[] = $catID->cat_id;
                }

                $postData = db()->Select($tblCol)
                    ->From($postCatTbl)
                    ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
                    ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
                    ->WhereEquals('post_status', 1)
                    ->WhereIn('cat_id', $catIDS)
                    ->Where("$postTbl.created_at", '<=', helper()->date())
                    ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(AppConfig::getAppPaginationMax());

                $postData = ['PostData' => $postData, 'CategoryData' => $catIDSResult];

            } catch (\Exception $exception){
                // log..
            }

            $fieldSettings = json_decode($category['field_settings'], true);
            $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'cat_content');
            $category = [...$fieldSettings, ...$category];

            $date = new \DateTime($category['created_at']);
            $category['created_at_words'] = strtoupper($date->format('j M, Y'));
            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());

            event()->dispatch($this->getCouponData()->getOnPostCategoryDefaultField());
            $slugs = event()->dispatch(new OnPostDefaultField())->getHiddenFieldSlug();

            # MoreData can't use the _fieldDetails here
            # MoreData can't use the _fieldDetails here
            unset($moreData['_fieldDetails']);
            $dataBundle = [...$category, ...$moreData, ...$postData];
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