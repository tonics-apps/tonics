<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Controllers;

use App\Modules\Core\Configs\AppConfig;
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
use App\Modules\Post\Helper\PostRedirection;
use App\Modules\Post\Rules\PostValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class PostCategoryController
{
    private PostData $postData;

    use Validator, PostValidationRules, UniqueSlug;

    public function __construct(PostData $postData)
    {
        $this->postData = $postData;
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

        $data = null;
        db(onGetDB: function (TonicsQuery $db) use ($table, $tblCol, &$data){
            $data = $db->Select($tblCol)
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
        });

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
        if ($this->getPostData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getPostData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        event()->dispatch($this->getPostData()->getOnPostCategoryDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Post/Views/Category/create', [
            'Categories' => $this->getPostData()->getCategoryHTMLSelect(),
            'FieldItems' => $this->getFieldData()->generateFieldWithFieldSlug($this->getPostData()->getOnPostCategoryDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
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
        $dbTx = db();
        try {
            $dbTx->beginTransaction();
            $category = $this->postData->createCategory();
            $categoryReturning = $this->postData->insertForPost($category, PostData::Category_INT);
            $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning, $this->postData);
            event()->dispatch($onPostCategoryCreate);
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();

            apcu_clear_cache();
            session()->flash(['Post Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.category.edit', ['category' => $onPostCategoryCreate->getCatSlug()]));
        }catch (Exception $exception){
            // Log..
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
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
            $category = $this->postData->createCategory();
            $categoryReturning = $this->postData->insertForPost($category, PostData::Category_INT);

        }catch (\Exception $e){
            helper()->sendMsg('PostCategoryController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }
        $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning, $this->postData);
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
        $tblCol = '*, CONCAT("/admin/posts/category/", cat_slug, "/edit" ) as _edit_link, CONCAT("/categories/", cat_slug) as _preview_link';

        $table = Tables::getTable(Tables::CATEGORIES);
        $category = null;
        db(onGetDB: function (TonicsQuery $db) use ($slug, $table, $tblCol, &$category){
            $category = $db->Select($tblCol)
                ->From($table)->WhereEquals('cat_slug', $slug)->FetchFirst();
        });

        if (!is_object($category)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($category->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$category;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$category];
        }

        event()->dispatch($this->getPostData()->getOnPostCategoryDefaultField());
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getPostData()->getOnPostCategoryDefaultField()->getFieldSlug(), $fieldSettings);
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

        if (input()->fromPost()->hasValue('cat_parent_id') && input()->fromPost()->hasValue('cat_id')){
            $catParentID = input()->fromPost()->retrieve('cat_parent_id');
            $catID = input()->fromPost()->retrieve('cat_id');
            $category = null;
            db(onGetDB: function ($db) use ($slug, &$category){
                $category = $db->Select('*')->From($this->getPostData()->getCategoryTable())->WhereEquals('cat_slug', $slug)->FetchFirst();
            });

            // Category Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            if ($catParentID === $catID){
                $_POST['cat_parent_id'] = $category->cat_parent_id;
                // Log..
                // Error Message is: Category Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            }
        }

        $categoryToUpdate = $this->postData->createCategory();
        $categoryToUpdate['cat_slug'] = helper()->slug(input()->fromPost()->retrieve('cat_slug'));

        db(onGetDB: function ($db) use ($slug, $categoryToUpdate) {
            $db->FastUpdate($this->postData->getCategoryTable(), $categoryToUpdate, db()->Where('cat_slug', '=', $slug));
        });

        $slug = $categoryToUpdate['cat_slug'];

        apcu_clear_cache();
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
        return $this->getPostData()->dataTableUpdateMultiple([
            'id' => 'cat_id',
            'table' => Tables::getTable(Tables::CATEGORIES),
            'rules' => $this->postCategoryUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getPostData()->dataTableDeleteMultiple([
            'id' => 'cat_id',
            'table' => Tables::getTable(Tables::CATEGORIES),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID){
                $category = null;
                db(onGetDB: function (TonicsQuery $db) use ($slugID, &$category){
                    $category = $db->Select("slug_id, cat_slug")
                        ->From($this->getPostData()->getCategoryTable())->WhereEquals('slug_id', $slugID)->FetchFirst();
                });

                if (isset($category->slug_id) && isset($category->cat_slug)){
                    return PostRedirection::getCategoryAbsoluteURLPath((array)$category);
                }
                return false;
            }, onSlugState: function ($slug){

            $category = null;
            db(onGetDB: function (TonicsQuery $db) use ($slug, &$category){
                $category = $db->Select("slug_id, cat_slug")
                    ->From($this->getPostData()->getCategoryTable())->WhereEquals('cat_slug', $slug)->FetchFirst();
            });
            if (isset($category->slug_id) && isset($category->cat_slug)){
                return PostRedirection::getCategoryAbsoluteURLPath((array)$category);
            }
            return false;
        });

        $redirection->runStates();
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->getPostData()->getFieldData();
    }

}