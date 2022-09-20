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

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Roles;
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
use App\Modules\Post\Rules\PostValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;
use stdClass;

class PostsController
{
    use UniqueSlug, Validator, PostValidationRules;

    private PostData $postData;
    private UserData $userData;

    /**
     * @param PostData $postData
     * @param UserData $userData
     */
    public function __construct(PostData $postData, UserData $userData)
    {
        $this->postData = $postData;
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
            $categoriesSelectDataAttribute .= $category->cat_id . '::' . $category->cat_slug . ',';
        }

        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $categoriesSelectDataAttribute = rtrim($categoriesSelectDataAttribute, ',');
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::POSTS . '::' . 'post_id', 'title' => 'Post ID', 'minmax' => '50px, .5fr'],
            ['type' => 'text', 'slug' => Tables::POSTS . '::' . 'post_title', 'title' => 'Title', 'minmax' => '150px, 2fr'],
            ['type' => 'select', 'slug' => Tables::POST_CATEGORIES . '::' . 'fk_cat_id', 'title' => 'Category', 'dataAttribute' => "data-select_data=$categoriesSelectDataAttribute", 'minmax' => '150px, 1fr'],
            ['type' => 'date_time_local', 'slug' => Tables::POSTS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr'],
        ];

        $tblCol = table()->pick([$postTbl => ['post_id', 'post_title']]) . ', CONCAT( cat_id, "::", cat_slug ) as fk_cat_id , ' .
            table()->pickTable($postTbl, ['updated_at']);


        $postData = db()->Select($tblCol)
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('post_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('post_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('post_title', url()->getParam('query'));

            })->when(url()->hasParamAndValue('cat'), function (TonicsQuery $db) {
                $db->WhereIn('cat_id', url()->getParam('cat'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($postTbl) {
                $db->WhereBetween(table()->pickTable($postTbl, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Post/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'postData' => $postData ?? []
            ],
            'SiteURL' => AppConfig::getAppUrl(),
            'DefaultCategoriesMetaBox' => $this->getPostData()->categoryCheckBoxListing($categories, url()->getParam('cat') ?? [], type: 'checkbox'),
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
            if (($deleted = $this->deleteMultiple($entityBag))) {
                response()->onSuccess([], "$deleted Deleted", more: $deleted);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getPostData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if (($updated = $this->updateMultiple($entityBag))) {
                response()->onSuccess([], "$updated Updated", more: $updated);
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
        event()->dispatch($this->getPostData()->getOnPostDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Post/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug($this->getPostData()->getOnPostDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
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

        if (input()->fromPost()->hasValue('post_slug') === false) {
            $_POST['post_slug'] = helper()->slug(input()->fromPost()->retrieve('post_title'));
        }

        $this->postData->setDefaultPostCategoryIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('posts.create'));
        }

        $post = $this->postData->createPost(['token']);

        try {
            db()->beginTransaction();
            $onBeforePostSave = new OnBeforePostSave($post);
            event()->dispatch($onBeforePostSave);
            $postReturning = $this->postData->insertForPost($onBeforePostSave->getData(), PostData::Post_INT, $this->postData->getPostColumns());

            if (is_object($postReturning)){
                $postReturning->fk_cat_id = input()->fromPost()->retrieve('fk_cat_id', '');
            }

            $onPostCreate = new OnPostCreate($postReturning, $this->postData);
            event()->dispatch($onPostCreate);

            db()->commit();

            session()->flash(['Post Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.edit', ['post' => $onPostCreate->getPostSlug()]));
        }catch (\Exception $exception){
            // log..
            db()->rollBack();
            session()->flash(['An Error Occurred, Creating Post'], input()->fromPost()->all());
            redirect(route('posts.create'));
        }

    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function storeFromImport(array $postData): bool|object
    {
        $previousPOSTGlobal = $_POST;
        try {
            foreach ($postData as $k => $cat) {
                $_POST[$k] = $cat;
            }
            $this->postData->setDefaultPostCategoryIfNotSet();
            $validator = $this->getValidator()->make($_POST, $this->postStoreRule());
            if ($validator->fails()) {
                helper()->sendMsg('PostsController::storeFromImport()', json_encode($validator->getErrors()), 'issue');
                return false;
            }
            $post = $this->postData->createPost(['token']);
            $postReturning = $this->postData->insertForPost($post, PostData::Post_INT, $this->postData->getPostColumns());
        } catch (\Exception $e) {
            helper()->sendMsg('PostsController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }

        $onPostCreate = new OnPostCreate($postReturning, $this->postData);
        event()->dispatch($onPostCreate);
        $_POST = $previousPOSTGlobal;
        return $onPostCreate;
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $post = $this->postData->selectWithConditionFromPost(['*'], "post_slug = ?", [$slug]);
        if (!is_object($post)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($post->field_settings, true);
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (is_array($oldFormInput)) {
            $oldFormInputFieldSettings = json_decode($oldFormInput['field_settings'], true) ?? [];
            $fieldSettings = [...$fieldSettings, ...$oldFormInputFieldSettings];
        }

        $fieldSettings = $this->getFieldData()->handleEditorMode($fieldSettings, 'post_content');

        if (empty($fieldSettings)) {
            $fieldSettings = (array)$post;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$post];
        }

        event()->dispatch($this->getPostData()->getOnPostDefaultField());

        $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getPostData()->getOnPostDefaultField()->getFieldSlug(), $fieldSettings);
        $fieldItems = $fieldForm->getHTMLFrag();
        view('Modules::Post/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'Data' => $post,
            'FieldItems' => $fieldItems,
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {

        $this->postData->setDefaultPostCategoryIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('posts.edit', [$slug]));
        }

        $postToUpdate = $this->postData->createPost(['token']);
        $postToUpdate['post_slug'] = helper()->slug(input()->fromPost()->retrieve('post_slug'));
        event()->dispatch(new OnBeforePostSave($postToUpdate));

        try {
            db()->beginTransaction();
            db()->FastUpdate($this->postData->getPostTable(), $postToUpdate, db()->Where('post_slug', '=', $slug));

            $postToUpdate['fk_cat_id'] = input()->fromPost()->retrieve('fk_cat_id', '');
            $postToUpdate['post_id'] = input()->fromPost()->retrieve('post_id', '');
            $onPostUpdate = new OnPostUpdate((object)$postToUpdate, $this->postData);
            event()->dispatch($onPostUpdate);

            db()->commit();

            $slug = $postToUpdate['post_slug'];
            session()->flash(['Post Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.edit', ['post' => $slug]));

        } catch (\Exception $exception){
            db()->rollBack();
            // log..
            session()->flash(['Error Occur Updating Post'], $postToUpdate);
            redirect(route('posts.edit', ['post' => $slug]));
        }
    }

    /**
     * @throws \Exception
     */
    protected function deleteMultiple($entityBag): bool|int
    {
        $toDelete = [];
        try {
            $deleteItems = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
            $db = db();
            foreach ($deleteItems as $deleteItem) {
                if (is_object($deleteItem) && property_exists($deleteItem, 'post_id')) {
                    $toDelete[] = $deleteItem->post_id;
                }
            }

            $db->FastDelete(Tables::getTable(Tables::POSTS), db()->WhereIn('id', $toDelete));
            return $db->getRowCount();
        } catch (\Exception $exception) {
            // log..
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    protected function updateMultiple($entityBag)
    {
        $postTable = Tables::getTable(Tables::POSTS);
        $postCatTable = Tables::getTable(Tables::POST_CATEGORIES);
        $updateTables = $postTable . ', ' . $postCatTable;

        $affected = 0;
        try {
            $updateItems = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            db()->beginTransaction();
            foreach ($updateItems as $updateItem) {
                $db = db();
                $postUpdate = []; $colForEvent = [];
                foreach ($updateItem as $col => $value){
                    $tblCol = explode('::', $col) ?? [];

                    # Table and column is invalid, should be in the format table::col
                    if (count($tblCol) !== 2){
                        throw new \Exception("DataTable::Invalid table and column, should be in the format table::col");
                    }

                    # Col doesn't exist, we throw an exception
                    if (!Tables::hasColumn($tblCol[0], ($tblCol[1]))){
                        throw new \Exception("DataTable::Invalid col name {$tblCol[1]}");
                    }

                    # We get the column (this also validates the table)
                    $setCol = table()->getColumn(Tables::getTable($tblCol[0]), $tblCol[1]);

                    if ($tblCol[1] === 'fk_cat_id'){
                        $value = explode('::', $value);
                        if (key_exists(0, $value)){
                            $colForEvent[$tblCol[1]] = $value[0];
                        } else {
                            return false;
                        }
                    }else {
                        $colForEvent[$tblCol[1]] = $value;
                        $postUpdate[$setCol] = $value;
                    }
                }

                # Validate The col and type
                $validator = $this->getValidator()->make($colForEvent, $this->postUpdateMultipleRule());
                if ($validator->fails()) {
                    throw new \Exception("DataTable::Validation Error {$validator->errorsAsString()}");
                }

                $postID = $postUpdate[table()->getColumn($postTable, 'post_id')];
                $db->FastUpdate($this->postData->getPostTable(), $postUpdate, db()->Where('post_id', '=', $postID));

                $affected += $db->getRowCount();
                $onPostUpdate = new OnPostUpdate((object)$colForEvent, $this->postData);
                event()->dispatch($onPostUpdate);
            }
            db()->commit();
        } catch (\Exception $exception) {
            $affected = 0;
            db()->rollBack();
            // log..
        }

        return $affected;
    }


    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID) {
                $post = $this->getPostData()
                    ->selectWithConditionFromPost(['*'], "slug_id = ?", [$slugID]);
                if (isset($post->slug_id) && isset($post->post_slug)) {
                    return "/posts/$post->slug_id/$post->post_slug";
                }
                return false;
            }, onSlugState: function ($slug) {
            $post = $this->getPostData()
                ->selectWithConditionFromPost(['*'], "post_slug = ?", [$slug]);
            if (isset($post->slug_id) && isset($post->post_slug)) {
                return "/posts/$post->slug_id/$post->post_slug";
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
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->getPostData()->getFieldData();
    }

}