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
        $userTable = Tables::getTable(Tables::USERS);

        $categoriesSelectDataAttribute = rtrim($categoriesSelectDataAttribute, ',');
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::POSTS . '::' . 'post_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'post_id'],
            ['type' => 'text', 'slug' => Tables::POSTS . '::' . 'post_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'post_title'],
            ['type' => 'TONICS_MEDIA_FEATURE_LINK', 'slug' => Tables::POSTS . '::' . 'image_url', 'title' => 'Image', 'minmax' => '150px, 1fr', 'td' => 'image_url'],
            ['type' => 'select_multiple', 'slug' => Tables::POST_CATEGORIES . '::' . 'fk_cat_id', 'title' => 'Category', 'select_data' => "$categoriesSelectDataAttribute", 'minmax' => '300px, 1fr', 'td' => 'fk_cat_id'],
            ['type' => 'date_time_local', 'slug' => Tables::POSTS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $postData = db()->Select(PostData::getPostTableJoiningRelatedColumns())
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($postTbl, ['user_id']))
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

            })
            ->GroupBy('post_id')
            ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Post/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $postData ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

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

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $dbTx = db();
        try {
            $dbTx->beginTransaction();
            $post = $this->postData->createPost(['token']);
            $onBeforePostSave = new OnBeforePostSave($post);
            event()->dispatch($onBeforePostSave);
            $postReturning = $this->postData->insertForPost($onBeforePostSave->getData(), PostData::Post_INT, $this->postData->getPostColumns());
            if (is_object($postReturning)) {
                $postReturning->fk_cat_id = input()->fromPost()->retrieve('fk_cat_id', '');
            }

            $onPostCreate = new OnPostCreate($postReturning, $this->postData);
            event()->dispatch($onPostCreate);
            $dbTx->commit();

            session()->flash(['Post Created'], type: Session::SessionCategories_FlashMessageSuccess);
            apcu_clear_cache();
            redirect(route('posts.edit', ['post' => $onPostCreate->getPostSlug()]));
        } catch (\Exception $exception) {
            // log..
            $dbTx->rollBack();
            session()->flash(['An Error Occurred, Creating Post'], input()->fromPost()->all());
            redirect(route('posts.create'));
        }

    }

    /**
     * @param array $postData
     * @return bool|object
     * @throws \Exception
     */
    public function storeFromImport(array $postData): bool|object
    {
        $previousPOSTGlobal = $_POST;
        $db = db();
        try {
            $db->beginTransaction();
            foreach ($postData as $k => $cat) {
                $_POST[$k] = $cat;
            }
            $this->postData->setDefaultPostCategoryIfNotSet();
            if (isset($_POST['fk_cat_id']) && !is_array($_POST['fk_cat_id'])){
                $_POST['fk_cat_id'] = [$_POST['fk_cat_id']];
            }

            $validator = $this->getValidator()->make($_POST, $this->postStoreRule());
            if ($validator->fails()) {
                helper()->sendMsg('PostsController::storeFromImport()', json_encode($validator->getErrors()), 'issue');
                return false;
            }

            $post = $this->postData->createPost(['token']);
            $postReturning = $this->postData->insertForPost($post, PostData::Post_INT, $this->postData->getPostColumns());
            if (is_object($postReturning)) {
                $postReturning->fk_cat_id = input()->fromPost()->retrieve('fk_cat_id', '');
            }

            $onPostCreate = new OnPostCreate($postReturning, $this->postData);
            event()->dispatch($onPostCreate);
            $_POST = $previousPOSTGlobal;

            $db->commit();
            return $onPostCreate;
        } catch (\Exception $e) {
            $db->rollBack();
            helper()->sendMsg('PostsController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }

    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);
        $tblCol = table()->pickTableExcept($postTbl, [])
            . ', GROUP_CONCAT(CONCAT(cat_id) ) as fk_cat_id'
            . ', CONCAT_WS("/", "/posts", post_slug) as _preview_link ';

        $post = db()->Select($tblCol)
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->WhereEquals('post_slug', $slug)
            ->GroupBy('post_id')->FetchFirst();

        if (!is_object($post)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        if (isset($post->fk_cat_id)){
            $post->fk_cat_id = explode(',', $post->fk_cat_id);
        }

        $fieldSettings = json_decode($post->field_settings, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$post;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$post];
        }

        event()->dispatch($this->getPostData()->getOnPostDefaultField());

        # Since Cat_ID would be multiple, if the multiple version doesn't exist, add it...
        if (isset($fieldSettings['fk_cat_id']) && !isset($fieldSettings['fk_cat_id[]'])){
            $fieldSettings['fk_cat_id[]'] = !is_array($fieldSettings['fk_cat_id']) ? [$fieldSettings['fk_cat_id']] : $fieldSettings['fk_cat_id'];
        }

        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getPostData()->getOnPostDefaultField()->getFieldSlug(), $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $post);
        }

        view('Modules::Post/Views/edit', [
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
        $this->postData->setDefaultPostCategoryIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('posts.edit', [$slug]));
        }

        $db = db();
        $db->beginTransaction();
        $postToUpdate = $this->postData->createPost(['token']);

        try {
            $postToUpdate['post_slug'] = helper()->slug(input()->fromPost()->retrieve('post_slug'));
            event()->dispatch(new OnBeforePostSave($postToUpdate));
            db()->FastUpdate($this->postData->getPostTable(), $postToUpdate, db()->Where('post_slug', '=', $slug));

            $postToUpdate['fk_cat_id'] = input()->fromPost()->retrieve('fk_cat_id', '');
            $postToUpdate['post_id'] = input()->fromPost()->retrieve('post_id', '');
            $onPostUpdate = new OnPostUpdate((object)$postToUpdate, $this->postData);
            event()->dispatch($onPostUpdate);

            $db->commit();

            # For Fields
            apcu_clear_cache();
            $slug = $postToUpdate['post_slug'];
            if (input()->fromPost()->has('_fieldErrorEmitted') === true){
                session()->flash(['Post Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
            } else {
                session()->flash(['Post Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            }
            redirect(route('posts.edit', ['post' => $slug]));

        } catch (\Exception $exception) {
            $db->rollBack();
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
            foreach ($deleteItems as $deleteItem) {
                foreach ($deleteItem as $col => $value) {
                    $tblCol = $this->getPostData()->validateTableColumnForDataTable($col);
                    if ($tblCol[1] === 'post_id') {
                        $toDelete[] = $value;
                    }
                }
            }

            db()->FastDelete(Tables::getTable(Tables::POSTS), db()->WhereIn('post_id', $toDelete));
            apcu_clear_cache();
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
        $dbTx = db();
        try {
            $updateItems = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            $dbTx->beginTransaction();
            foreach ($updateItems as $updateItem) {
                $db = db();
                $postUpdate = [];
                $colForEvent = [];
                foreach ($updateItem as $col => $value) {
                    $tblCol = $this->getPostData()->validateTableColumnForDataTable($col);

                    # We get the column (this also validates the table)
                    $setCol = table()->getColumn(Tables::getTable($tblCol[0]), $tblCol[1]);

                    if ($tblCol[1] === 'fk_cat_id') {
                        $categories = explode(',', $value);
                        foreach ($categories as $category){
                            $category = explode('::', $category);
                            if (key_exists(0, $category) && !empty($category[0])) {
                                $colForEvent['fk_cat_id'][] = $category[0];
                            }
                        }

                        // Set to Default Category If Empty
                        if (empty($colForEvent['fk_cat_id'])){
                            $findDefault = $this->postData->selectWithConditionFromCategory(['cat_slug', 'cat_id'], "cat_slug = ?", ['default-category']);
                            if (is_object($findDefault) && isset($findDefault->cat_id)) {
                                $colForEvent['fk_cat_id'] = [$findDefault->cat_id];
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

                $postID = $postUpdate[table()->getColumn($postTable, 'post_id')];
                $db->FastUpdate($this->postData->getPostTable(), $postUpdate, db()->Where('post_id', '=', $postID));

                $onPostUpdate = new OnPostUpdate((object)$colForEvent, $this->postData);
                event()->dispatch($onPostUpdate);
            }
            $dbTx->commit();
            return true;
        } catch (\Exception $exception) {
            $dbTx->rollBack();
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
                $post = $this->getPostData()
                    ->selectWithConditionFromPost(['*'], "slug_id = ?", [$slugID]);
                if (isset($post->slug_id) && isset($post->post_slug)) {
                     return PostRedirection::getPostAbsoluteURLPath((array)$post);
                }
                return false;
            }, onSlugState: function ($slug) {
            $post = $this->getPostData()
                ->selectWithConditionFromPost(['*'], "post_slug = ?", [$slug]);
            if (isset($post->slug_id) && isset($post->post_slug)) {
                return PostRedirection::getPostAbsoluteURLPath((array)$post);
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