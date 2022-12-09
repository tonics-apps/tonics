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
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCategoryDefaultField;
use App\Modules\Post\Rules\PostValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class CouponTypeController
{
    private CouponData $couponData;
    private UserData $userData;

    use Validator, PostValidationRules, UniqueSlug;

    public function __construct(CouponData $couponData, UserData $userData)
    {
        $this->couponData = $couponData;
        $this->userData = $userData;
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        $table = Tables::getTable(Tables::CATEGORIES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::CATEGORIES . '::' . 'cat_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'cat_id'],
            ['type' => 'text', 'slug' => Tables::CATEGORIES . '::' . 'cat_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'cat_name'],
            ['type' => 'date_time_local', 'slug' => Tables::CATEGORIES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = '*, CONCAT("/admin/posts/category/", cat_slug, "/edit" ) as _edit_link, CONCAT("/categories/", cat_slug) as _preview_link';

        $data = db()->Select($tblCol)
            ->From($table)
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('cat_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('cat_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('cat_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Post/Views/Category/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

            ],
            'SiteURL' => AppConfig::getAppUrl(),
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
     * @throws \Exception
     */
    public function create()
    {
        event()->dispatch($this->getCouponData()->getOnCouponTypeDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCoupon/Views/CouponType/create', [
            'Categories' => $this->getCouponData()->getCouponTypeHTMLSelect(),
            'FieldItems' => $this->getFieldData()->generateFieldWithFieldSlug($this->getCouponData()->getOnCouponTypeDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        if (input()->fromPost()->hasValue('created_at') === false){
            $_POST['created_at'] = helper()->date();
        }

        if (input()->fromPost()->hasValue('cat_slug') === false){
            $_POST['cat_slug'] = helper()->slug(input()->fromPost()->retrieve('cat_name'));
        }

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postCategoryStoreRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('posts.category.create'));
        }

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $db = db();
        try {
            $db->beginTransaction();
            $category = $this->couponData->createCategory();
            $categoryReturning = $this->couponData->insertForPost($category, PostData::Category_INT);
            $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning, $this->couponData);
            event()->dispatch($onPostCategoryCreate);
            $db->commit();

            session()->flash(['Post Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.category.edit', ['category' => $onPostCategoryCreate->getCatSlug()]));
        }catch (Exception $exception){
            // Log..
            $db->rollBack();
            session()->flash(['An Error Occurred, Creating Post Category'], input()->fromPost()->all());
            redirect(route('posts.category.create'));
        }

    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function storeFromImport(array $categoryData): bool|object
    {
        $previousPOSTGlobal = $_POST;
        $validator = $this->getValidator()->make($categoryData, $this->postCategoryStoreRule());
        try {
            if ($validator->fails()){
                helper()->sendMsg('PostCategoryController::storeFromImport()', json_encode($validator->getErrors()), 'issue');
                return false;
            }
            foreach ($categoryData as $k => $cat){
                $_POST[$k] = $cat;
            }
            $category = $this->couponData->createCategory();
            $categoryReturning = $this->couponData->insertForPost($category, PostData::Category_INT);

        }catch (\Exception $e){
            helper()->sendMsg('PostCategoryController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }
        $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning, $this->couponData);
        event()->dispatch($onPostCategoryCreate);
        $_POST = $previousPOSTGlobal;
        return $onPostCategoryCreate;

    }

    /**
     * @param string $slug
     * @throws \Exception
     */
    public function edit(string $slug): void
    {
        $category = $this->couponData->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$slug]);

        if (!is_object($category)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($category->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$category;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$category];
        }

        event()->dispatch($this->getCouponData()->getOnPostCategoryDefaultField());
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getCouponData()->getOnPostCategoryDefaultField()->getFieldSlug(), $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $category);
        }

        view('Modules::Post/Views/Category/edit', [
            'FieldItems' => $htmlFrag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug): void
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postCategoryUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('posts.category.edit', [$slug]));
        }

        $categoryToUpdate = $this->couponData->createCategory();
        $categoryToUpdate['cat_slug'] = helper()->slug(input()->fromPost()->retrieve('cat_slug'));

        db()->FastUpdate($this->couponData->getCategoryTable(), $categoryToUpdate, db()->Where('cat_slug', '=', $slug));
        $slug = $categoryToUpdate['cat_slug'];

        if (input()->fromPost()->has('_fieldErrorEmitted') === true){
            session()->flash(['Post Category Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
        } else {
            session()->flash(['Post Category Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        }
        redirect(route('posts.category.edit', ['category' => $slug]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getCouponData()->dataTableUpdateMultiple('cat_id', Tables::getTable(Tables::CATEGORIES), $entityBag, $this->postCategoryUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getCouponData()->dataTableDeleteMultiple('cat_id', Tables::getTable(Tables::CATEGORIES), $entityBag);
    }

    /**
     * @param string $slug
     * @return void
     * @throws Exception
     */
    public function delete(string $slug): void
    {
        try {
            $this->getCouponData()->deleteWithCondition(whereCondition: "cat_slug = ?", parameter: [$slug], table: $this->getCouponData()->getCategoryTable());
            session()->flash(['Category Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.category.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                default:
                    session()->flash(['Failed To Delete Category']);
                    break;
            }
            redirect(route('Category'));
        }
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID){
                $category = $this->getCouponData()
                    ->selectWithConditionFromCategory(['*'], "slug_id = ?", [$slugID]);
                if (isset($category->slug_id) && isset($category->cat_slug)){
                    return "/categories/$category->slug_id/$category->cat_slug";
                }
                return false;
            }, onSlugState: function ($slug){
            $category = $this->getCouponData()
                ->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$slug]);
            if (isset($category->slug_id) && isset($category->cat_slug)){
                return "/categories/$category->slug_id/$category->cat_slug";
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
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->getCouponData()->getFieldData();
    }

    /**
     * @return UserData
     */
    public function getUserData(): UserData
    {
        return $this->userData;
    }

}