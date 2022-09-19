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
use App\Modules\Post\Events\OnPostDefaultField;
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
       $categories_meta = $this->getPostData()->getCategoriesPaginationData();
        # For Category Meta Box API
       $this->getPostData()->categoryMetaBox($categories_meta);

        $categoryTable = Tables::getTable(Tables::CATEGORIES);
        $categories = db()->Select(table()->pickTableExcept($categoryTable, ['field_settings', 'created_at', 'updated_at']))
            ->From(Tables::getTable(Tables::CATEGORIES))->FetchResult();

        $categoriesSelectDataAttribute = '';
        foreach ($categories as $category) {
            $categoriesSelectDataAttribute .= $category->cat_slug . ',';
        }

        $categoriesSelectDataAttribute = rtrim($categoriesSelectDataAttribute, ',');
        $dataTableHeaders = [
            ['type' => '', 'slug' => 'post_id', 'title' => 'Post ID', 'minmax' => '50px, 1fr'],
            ['type' => '', 'slug' => 'slug_id', 'title' => 'Slug ID', 'minmax' => '150px, 1fr'],
            ['type' => 'text', 'slug' => 'post_title', 'title' => 'Title', 'minmax' => '150px, 2fr'],
            ['type' => 'select', 'slug' => 'cat_slug', 'title' => 'Category', 'dataAttribute' => "data-select_data=$categoriesSelectDataAttribute", 'minmax' => '150px, 1fr'],
            ['type' => 'date_time_local', 'slug' => 'created_at', 'title' => 'Date Created', 'minmax' => '150px, 1fr'],
            ['type' => 'date_time_local', 'slug' => 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr'],
        ];

        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $tblCol = table()->pick([$postTbl => ['post_id', 'slug_id', 'post_title'], $CatTbl => ['cat_slug']]) . ', ' .
            table()->pickTable($postTbl, ['created_at', 'updated_at']);

        $postData = db()->Select($tblCol)
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) { $db->WhereEquals('post_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) { $db->WhereEquals('post_status', 1);

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
                'postData' => $postData,
            ],
            'SiteURL' => AppConfig::getAppUrl(),
            'DefaultCategoriesMetaBox' => $this->getPostData()->categoryCheckBoxListing($categories_meta, url()->getParam('cat') ?? [], type: 'checkbox'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable()
    {
        $entityBag = null;
        if ($this->getPostData()->isDataTableType(AbstractDataLayer::DataTableEventTypeFilter,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            $filterData = $this->dataTableRetrievePostData($entityBag);
            response()->onSuccess($filterData ?? []);
        }
    }

    /**
     * @param $entityBag
     * @return array|bool
     * @throws \Exception
     */
    private function dataTableRetrievePostData($entityBag): bool|array
    {
        $filterOption = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveFilterOption, $entityBag);
        $lastElement = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveLastElement, $entityBag);
        $pageSize = $this->getPostData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrievePageSize, $entityBag);

        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);
        $tblCol = table()->except([$postTbl => ['field_settings'], $CatTbl => ['field_settings', 'slug_id', 'created_at', 'updated_at']]);

        return db()->Select($tblCol)->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->when(isset($filterOption['query']) && !empty($filterOption['query']), function (TonicsQuery $db) use ($filterOption) {
                $db->WhereLike('post_title', $filterOption['query']);

            })->when(isset($filterOption['status']),
                function (TonicsQuery $db) use ($filterOption) {
                    $db->WhereEquals('post_status', $filterOption['status']);
                }, function (TonicsQuery $db) {
                    $db->WhereEquals('post_status', 1);

                })->when((isset($filterOption['cat[]']) && !empty($filterOption['cat[]'])) && is_array($filterOption['cat[]']), function (TonicsQuery $db) use ($filterOption) {
                $db->WhereIn('cat_id', $filterOption['cat[]']);

            })->when((isset($filterOption['start_date']) && !empty($filterOption['start_date']))
                && (isset($filterOption['end_date']) && !empty($filterOption['end_date'])),
                function (TonicsQuery $db) use ($postTbl, $filterOption) {
                    $db->WhereBetween(table()->pickTable($postTbl, ['created_at']), db()->DateFormat($filterOption['start_date']), db()->DateFormat($filterOption['end_date']));

            })->when(isset($lastRowDataSet['post_id']) && !empty($lastRowDataSet['post_id']),
                function (TonicsQuery $db) use ($lastRowDataSet, $pageSize, $filterOption) {
                $db->Where('post_id', '>', (int)$lastRowDataSet['post_id'])->OrderBy('post_id')->Take($pageSize[0] ?? AppConfig::getAppPaginationMax());
            }, function (TonicsQuery $db) use ($postTbl) {
                $db->OrderByDesc(table()->pickTable($postTbl, ['created_at']))->Take($pageSize[0] ?? AppConfig::getAppPaginationMax());

            })->FetchResult();
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

        $onBeforePostSave = new OnBeforePostSave($post);
        event()->dispatch($onBeforePostSave);
        $postReturning = $this->postData->insertForPost($onBeforePostSave->getData(), PostData::Post_INT, $this->postData->getPostColumns());

        $onPostCreate = new OnPostCreate($postReturning, $this->postData);
        event()->dispatch($onPostCreate);

        session()->flash(['Post Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.edit', ['post' => $onPostCreate->getPostSlug()]));
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

        db()->FastUpdate($this->postData->getPostTable(), $postToUpdate, db()->Where('post_slug', '=', $slug));
        $postToCategoryUpdate = [
            'fk_cat_id' => input()->fromPost()->retrieve('fk_cat_id', ''),
            'fk_post_id' => input()->fromPost()->retrieve('post_id', ''),
        ];
        db()->FastUpdate($this->postData->getPostToCategoryTable(), $postToCategoryUpdate, db()->Where('fk_post_id', '=', input()->fromPost()->retrieve('post_id')));

        $slug = $postToUpdate['post_slug'];
        $post = $this->postData->selectWithConditionFromPost(['*'], "post_slug = ?", [$slug]);
        $onPostUpdate = new OnPostUpdate($post, $this->postData);
        event()->dispatch($onPostUpdate);

        session()->flash(['Post Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.edit', ['post' => $slug]));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trash(string $slug)
    {
        $toUpdate = [
            'post_status' => -1
        ];

        db()->FastUpdate($this->postData->getPostTable(), $toUpdate, db()->Where('post_slug', '=', $slug));
        session()->flash(['Post Moved To Trash'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trashMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToTrash')) {
            session()->flash(['Nothing To Trash'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('posts.index'));
        }
        $itemsToTrash = array_map(function ($item) {
            $itemCopy = json_decode($item, true);
            $item = [];
            foreach ($itemCopy as $k => $v) {
                if (key_exists($k, array_flip($this->postData->getPostColumns()))) {
                    $item[$k] = $v;
                }
            }
            $item['post_status'] = '-1';
            return $item;
        }, input()->fromPost()->retrieve('itemsToTrash'));

        try {
            db()->insertOnDuplicate(Tables::getTable(Tables::POSTS), $itemsToTrash, ['post_status']);
        } catch (\Exception $e) {
            session()->flash(['Fail To Trash Post Items']);
            redirect(route('posts.index'));
        }
        session()->flash(['Posts Trashed'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.index'));
    }


    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getPostData()->deleteWithCondition(whereCondition: "post_slug = ?", parameter: [$slug], table: $this->getPostData()->getPostTable());
            session()->flash(['Post Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.index'));
        } catch (\Exception $e) {
            $errorCode = $e->getCode();
            switch ($errorCode) {
                default:
                    session()->flash(['Failed To Delete Post']);
                    break;
            }
            redirect(route('posts.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')) {
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('posts.index'));
        }

        $this->getPostData()->deleteMultiple(
            $this->getPostData()->getPostTable(),
            array_flip($this->getPostData()->getPostColumns()),
            'post_id',
            onSuccess: function () {
                session()->flash(['Post Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('posts.index'));
            },
            onError: function ($e) {
                $errorCode = $e->getCode();
                switch ($errorCode) {
                    default:
                        session()->flash(['Failed To Delete Post']);
                        break;
                }
                redirect(route('posts.index'));
            },
        );
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