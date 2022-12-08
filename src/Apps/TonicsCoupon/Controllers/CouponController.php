<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Controllers;

use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\Events\OnBeforeCouponSave;
use App\Apps\TonicsCoupon\Events\OnCouponCreate;
use App\Apps\TonicsCoupon\Rules\CouponValidationRules;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\States\CommonResourceRedirection;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnBeforePostSave;
use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Post\Events\OnPostUpdate;
use App\Modules\Post\Helper\PostRedirection;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class CouponController
{
    use UniqueSlug, Validator, CouponValidationRules;

    private CouponData $couponData;
    private UserData $userData;

    /**
     * @param CouponData $couponData
     * @param UserData $userData
     */
    public function __construct(CouponData $couponData, UserData $userData)
    {
        $this->couponData = $couponData;
        $this->userData = $userData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $categoryTable = Tables::getTable(Tables::CATEGORIES);
        $categories = db()->Select(table()->pickTableExcept($categoryTable, ['field_settings', 'created_at', 'updated_at']))
            ->From(Tables::getTable(Tables::CATEGORIES))->FetchResult();

        $categoriesSelectDataAttribute = '';
        foreach ($categories as $category) {
            $categoriesSelectDataAttribute .= $category->coupon_type_id . '::' . $category->coupon_type_slug . ',';
        }

        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::Coupon_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);
        $userTable = Tables::getTable(Tables::USERS);

        $categoriesSelectDataAttribute = rtrim($categoriesSelectDataAttribute, ',');
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::POSTS . '::' . 'coupon_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'coupon_id'],
            ['type' => 'text', 'slug' => Tables::POSTS . '::' . 'coupon_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'coupon_name'],
            ['type' => 'TONICS_MEDIA_FEATURE_LINK', 'slug' => Tables::POSTS . '::' . 'image_url', 'title' => 'Image', 'minmax' => '150px, 1fr', 'td' => 'image_url'],
            ['type' => 'select_multiple', 'slug' => Tables::coupon_CATEGORIES . '::' . 'fk_coupon_type_id', 'title' => 'Category', 'select_data' => "$categoriesSelectDataAttribute", 'minmax' => '300px, 1fr', 'td' => 'fk_coupon_type_id'],
            ['type' => 'date_time_local', 'slug' => Tables::POSTS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $postData = db()->Select(PostData::getPostTableJoiningRelatedColumns())
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['coupon_id']), table()->pickTable($postCatTbl, ['fk_coupon_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['coupon_type_id']), table()->pickTable($postCatTbl, ['fk_coupon_type_id']))
            ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($postTbl, ['user_id']))
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

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($postTbl) {
                $db->WhereBetween(table()->pickTable($postTbl, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })
            ->GroupBy('coupon_id')
            ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Post/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $postData ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

            ],
            'SiteURL' => AppConfig::getAppUrl(),
            'DefaultCategoriesMetaBox' => $this->getCouponData()->categoryCheckBoxListing($categories, url()->getParam('cat') ?? [], type: 'checkbox'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getCouponData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getCouponData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function create()
    {
        event()->dispatch($this->getCouponData()->getOnCouponDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCoupon/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug($this->getCouponData()->getOnCouponDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store(): void
    {

        if (input()->fromPost()->hasValue('created_at') === false) {
            $_POST['created_at'] = helper()->date();
        }

        if (input()->fromPost()->hasValue('coupon_slug') === false) {
            $_POST['coupon_slug'] = helper()->slug(input()->fromPost()->retrieve('coupon_name'));
        }

        $this->couponData->setDefaultCouponTypeIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->couponStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCoupon.create'));
        }

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $db = db();
        try {
            $db->beginTransaction();
            $coupon = $this->couponData->createCoupon(['token']);
            $onBeforePostSave = new OnBeforeCouponSave($coupon);
            event()->dispatch($onBeforePostSave);
            $couponReturning = $this->couponData->insertForCoupon($onBeforePostSave->getData(), CouponData::Coupon_INT, $this->couponData->getCouponColumns());
            if (is_object($couponReturning)) {
                $couponReturning->fk_coupon_type_id = input()->fromPost()->retrieve('fk_coupon_type_id', '');
            }

            $onCouponCreate = new OnCouponCreate($couponReturning, $this->couponData);
            event()->dispatch($onCouponCreate);
            $db->commit();

            session()->flash(['Coupon Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCoupon.edit', ['coupon' => $onCouponCreate->getCouponSlug()]));
        } catch (\Exception $exception) {
            // log..
            $db->rollBack();
            session()->flash(['An Error Occurred, Creating Coupon'], input()->fromPost()->all());
            redirect(route('tonicsCoupon.create'));
        }

    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $couponTable = $this->getCouponData()->getCouponTable();
        $couponToCouponTypeTable = $this->getCouponData()->getCouponToTypeTable();
        $couponTypeTable = $this->getCouponData()->getCouponTypeTable();

        $tblCol = table()->pickTableExcept($couponTable, [])
            . ', GROUP_CONCAT(CONCAT(coupon_type_id) ) as fk_coupon_type_id'
            . ', CONCAT_WS("/", "/coupon", coupon_slug) as _preview_link ';

        $coupon = db()->Select($tblCol)
            ->From($couponToCouponTypeTable)
            ->Join($couponTable, table()->pickTable($couponTable, ['coupon_id']), table()->pickTable($couponToCouponTypeTable, ['fk_coupon_id']))
            ->Join($couponTypeTable, table()->pickTable($couponTypeTable, ['coupon_type_id']), table()->pickTable($couponToCouponTypeTable, ['fk_coupon_type_id']))
            ->WhereEquals('coupon_slug', $slug)
            ->GroupBy('coupon_id')->FetchFirst();

        if (!is_object($coupon)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        if (isset($coupon->fk_coupon_type_id)){
            $coupon->fk_coupon_type_id = explode(',', $coupon->fk_coupon_type_id);
        }

        $fieldSettings = json_decode($coupon->field_settings, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$coupon;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$coupon];
        }

        event()->dispatch($this->getCouponData()->getOnCouponDefaultField());

        # Since coupon_type_ID would be multiple, if the multiple version doesn't exist, add it...
        if (isset($fieldSettings['fk_coupon_type_id']) && !isset($fieldSettings['fk_coupon_type_id[]'])){
            $fieldSettings['fk_coupon_type_id[]'] = !is_array($fieldSettings['fk_coupon_type_id']) ? [$fieldSettings['fk_coupon_type_id']] : $fieldSettings['fk_coupon_type_id'];
        }

        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getCouponData()->getOnCouponDefaultField()->getFieldSlug(), $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $coupon);
        }

        view('Apps::TonicsCoupon/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $htmlFrag,
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $this->couponData->setDefaultCouponTypeIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCoupon.edit', [$slug]));
        }

        $db = db();
        $db->beginTransaction();
        $postToUpdate = $this->couponData->createCoupon(['token']);

        try {
            $postToUpdate['coupon_slug'] = helper()->slug(input()->fromPost()->retrieve('coupon_slug'));
            event()->dispatch(new OnBeforePostSave($postToUpdate));
            db()->FastUpdate($this->couponData->getPostTable(), $postToUpdate, db()->Where('coupon_slug', '=', $slug));

            $postToUpdate['fk_coupon_type_id'] = input()->fromPost()->retrieve('fk_coupon_type_id', '');
            $postToUpdate['coupon_id'] = input()->fromPost()->retrieve('coupon_id', '');
            $onPostUpdate = new OnPostUpdate((object)$postToUpdate, $this->couponData);
            event()->dispatch($onPostUpdate);

            $db->commit();

            # For Fields
            $slug = $postToUpdate['coupon_slug'];
            if (input()->fromPost()->has('_fieldErrorEmitted') === true){
                session()->flash(['Post Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
            } else {
                session()->flash(['Post Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            }
            redirect(route('tonicsCoupon.edit', ['post' => $slug]));

        } catch (\Exception $exception) {
            $db->rollBack();
            // log..
            session()->flash(['Error Occur Updating Post'], $postToUpdate);
            redirect(route('tonicsCoupon.edit', ['post' => $slug]));
        }
    }

    /**
     * @throws \Exception
     */
    protected function deleteMultiple($entityBag): bool|int
    {
        $toDelete = [];
        try {
            $deleteItems = $this->getCouponData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
            foreach ($deleteItems as $deleteItem) {
                foreach ($deleteItem as $col => $value) {
                    $tblCol = $this->getCouponData()->validateTableColumnForDataTable($col);
                    if ($tblCol[1] === 'coupon_id') {
                        $toDelete[] = $value;
                    }
                }
            }

            db()->FastDelete(Tables::getTable(Tables::POSTS), db()->WhereIn('coupon_id', $toDelete));
            return true;
        } catch (\Exception $exception) {
            // log..
            return false;
        }
    }

    /**
     * @throws \Exception
     */
    protected function updateMultiple($entityBag)
    {
        $postTable = Tables::getTable(Tables::POSTS);
        try {
            $updateItems = $this->getCouponData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            db()->beginTransaction();
            foreach ($updateItems as $updateItem) {
                $db = db();
                $postUpdate = [];
                $colForEvent = [];
                foreach ($updateItem as $col => $value) {
                    $tblCol = $this->getCouponData()->validateTableColumnForDataTable($col);

                    # We get the column (this also validates the table)
                    $setCol = table()->getColumn(Tables::getTable($tblCol[0]), $tblCol[1]);

                    if ($tblCol[1] === 'fk_coupon_type_id') {
                        $categories = explode(',', $value);
                        foreach ($categories as $category){
                            $category = explode('::', $category);
                            if (key_exists(0, $category) && !empty($category[0])) {
                                $colForEvent['fk_coupon_type_id'][] = $category[0];
                            }
                        }

                        // Set to Default Category If Empty
                        if (empty($colForEvent['fk_coupon_type_id'])){
                            $findDefault = $this->couponData->selectWithConditionFromCategory(['coupon_type_slug', 'coupon_type_id'], "coupon_type_slug = ?", ['default-category']);
                            if (is_object($findDefault) && isset($findDefault->coupon_type_id)) {
                                $colForEvent['fk_coupon_type_id'] = [$findDefault->coupon_type_id];
                            }
                        }
                    } else {
                        $colForEvent[$tblCol[1]] = $value;
                        $postUpdate[$setCol] = $value;
                    }
                }

                # Validate The col and type
                $validator = $this->getValidator()->make($colForEvent, $this->postUpdateMultipleRule());
                if ($validator->fails()) {
                    throw new \Exception("DataTable::Validation Error {$validator->errorsAsString()}");
                }

                $postID = $postUpdate[table()->getColumn($postTable, 'coupon_id')];
                $db->FastUpdate($this->couponData->getPostTable(), $postUpdate, db()->Where('coupon_id', '=', $postID));

                $onPostUpdate = new OnPostUpdate((object)$colForEvent, $this->couponData);
                event()->dispatch($onPostUpdate);
            }
            db()->commit();
            return true;
        } catch (\Exception $exception) {
            db()->rollBack();
            return false;
            // log..
        }
    }


    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID) {
                $post = $this->getCouponData()
                    ->selectWithConditionFromPost(['*'], "slug_id = ?", [$slugID]);
                if (isset($post->slug_id) && isset($post->coupon_slug)) {
                     return PostRedirection::getPostAbsoluteURLPath((array)$post);
                }
                return false;
            }, onSlugState: function ($slug) {
            $post = $this->getCouponData()
                ->selectWithConditionFromPost(['*'], "coupon_slug = ?", [$slug]);
            if (isset($post->slug_id) && isset($post->coupon_slug)) {
                return PostRedirection::getPostAbsoluteURLPath((array)$post);
            }
            return false;
        });

        $redirection->runStates();
    }

    /**
     * @return CouponData
     */
    public function getCouponData(): CouponData
    {
        return $this->couponData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->getCouponData()->getFieldData();
    }

}