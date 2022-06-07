<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post\Controllers;

use App\Configs\AppConfig;
use App\Library\Authentication\Session;
use App\Library\CustomClasses\UniqueSlug;
use App\Library\SimpleState;
use App\Library\Tables;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Post\Events\OnPostDefaultField;
use App\Modules\Post\Events\OnPostUpdate;
use App\Modules\Post\Rules\PostValidationRules;
use App\Modules\Post\States\PostRedirection;
use JetBrains\PhpStorm\NoReturn;

class PostsController
{
    use UniqueSlug, Validator, PostValidationRules;

    private PostData $postData;
    private UserData $userData;
    private ?FieldData $fieldData;
    private ?OnPostDefaultField $onPostDefaultField;

    public function __construct(PostData $postData, UserData $userData, FieldData $fieldData = null, OnPostDefaultField $onPostDefaultField = null)
    {
        $this->postData = $postData;
        $this->userData = $userData;
        $this->fieldData = $fieldData;
        $this->onPostDefaultField = $onPostDefaultField;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $data = $this->getPostData()->generatePaginationData(
            $this->getPostData()->getPostPaginationColumns(),
            'post_title',
            $this->getPostData()->getPostTable());

        $postListing = '';
        if ($data !== null) {
            $postListing = $this->postData->adminPostListing($data->data, $this->postData->getPageStatus());
            unset($data->data);
        }

        view('Modules::Post/Views/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'PostListing' => $postListing
        ]);
    }


    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function create()
    {
        $this->fieldData->getFieldItemsAPI();

        event()->dispatch($this->onPostDefaultField);

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true);
        $oldFormInput = json_decode($oldFormInput, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Post/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldSelection' => $this->fieldData->getFieldsSelection($this->onPostDefaultField->getPostDefaultFieldSlug()),
            'FieldItems' => $this->fieldData->generateFieldWithFieldSlug($this->onPostDefaultField->getPostDefaultFieldSlug(), $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        if (input()->fromPost()->hasValue('created_at') === false){
            $_POST['created_at'] = helper()->date();
        }
        if (input()->fromPost()->hasValue('post_slug') === false){
            $_POST['post_slug'] = helper()->slug(input()->fromPost()->retrieve('post_title'));
        }

        $this->postData->setDefaultPostCategoryIfNotSet();
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all(), Session::SessionCategories_FlashMessageError);
            redirect(route('posts.create'));
        }

        $post = $this->postData->createPost(['token']);
        $postReturning = $this->postData->insertForPost($post, PostData::Post_INT, $this->postData->getPostColumns());

        $onPostCreate = new OnPostCreate($postReturning, $this->postData);
        event()->dispatch($onPostCreate);

        session()->flash(['Post Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.edit', ['post' => $onPostCreate->getPostSlug()]));
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function storeFromImport(array $postData): bool
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
            $post = $this->postData->createPost();
            $postReturning = $this->postData->insertForPost($post, PostData::Post_INT, $this->postData->getPostColumns());
        } catch (\Exception $e) {
            helper()->sendMsg('PostsController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }
        $onPostCreate = new OnPostCreate($postReturning, $this->postData);
        event()->dispatch($onPostCreate);
        $_POST = $previousPOSTGlobal;
        return true;
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $this->fieldData->getFieldItemsAPI();

        $post = $this->postData->selectWithConditionFromPost(['*'], "post_slug = ?", [$slug]);
        if (!is_object($post)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($post->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$post;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$post];
        }

        $onPostDefaultField = $this->onPostDefaultField;
        $fieldIDS = ($post->field_ids === null) ? [] : json_decode($post->field_ids, true);
        $onPostDefaultField->setPostDefaultFieldSlug($fieldIDS);
        event()->dispatch($onPostDefaultField);

        $fieldItems = $this->fieldData->generateFieldWithFieldSlug($onPostDefaultField->getPostDefaultFieldSlug(), $fieldSettings)->getHTMLFrag();
        view('Modules::Post/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'Data' => $post,
            'FieldSelection' => $this->fieldData->getFieldsSelection($onPostDefaultField->getPostDefaultFieldSlug()),
            'FieldItems' => $fieldItems
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
            session()->flash($validator->getErrors(),  input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageError);
            redirect(route('posts.edit', [$slug]));
        }

        $postToUpdate = $this->postData->createPost(['token']);
        $postToUpdate['post_slug'] = helper()->slug(input()->fromPost()->retrieve('post_slug'));
        $this->postData->updateWithCondition($postToUpdate, ['post_slug' => $slug], $this->postData->getPostTable());
        $postToCategoryUpdate = [
            'fk_cat_id' => input()->fromPost()->retrieve('fk_cat_id', ''),
            'fk_post_id' => input()->fromPost()->retrieve('post_id', ''),
        ];
        $this->postData->updateWithCondition($postToCategoryUpdate, ['fk_post_id' => input()->fromPost()->retrieve('post_id')], $this->postData->getPostToCategoryTable());

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
        $this->postData->updateWithCondition($toUpdate, ['post_slug' => $slug], $this->postData->getPostTable());
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
            session()->flash(['Fail To Trash Post Items'], type: Session::SessionCategories_FlashMessageError);
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
            session()->flash(['Artist Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('posts.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                default:
                    session()->flash(['Failed To Delete Post'], type: Session::SessionCategories_FlashMessageError);
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
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('posts.index'));
        }

        $this->getPostData()->deleteMultiple(
            $this->getPostData()->getPostTable(),
            array_flip($this->getPostData()->getPostColumns()),
            'post_id',
            onSuccess: function (){
                session()->flash(['Post Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('posts.index'));
            },
            onError: function ($e){
                $errorCode = $e->getCode();
                switch ($errorCode){
                    default:
                        session()->flash(['Failed To Delete Post'], type: Session::SessionCategories_FlashMessageError);
                        break;
                }
                redirect(route('posts.index'));
            },
        );
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id)
    {

        $postRedirection = new PostRedirection($this->getPostData());
        $postRedirection->setCurrentState(PostRedirection::OnPostInitialState);
        $postRedirection->setRequest(request());

        $postRedirection->runStates(false);
        if ($postRedirection->getStateResult() === SimpleState::DONE) {
            redirect($postRedirection->getIntendedPostURL());
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
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
        return $this->fieldData;
    }

}
