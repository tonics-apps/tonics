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

        $category = $this->postData->createCategory();
        $categoryReturning = $this->postData->insertForPost($category, PostData::Category_INT);

        $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning, $this->postData);
        event()->dispatch($onPostCategoryCreate);

        session()->flash(['Post Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.edit', ['category' => $onPostCategoryCreate->getCatSlug()]));
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
        $category = $this->postData->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$slug]);

        if (!is_object($category)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($category->field_settings, true);
        $fieldSettings = $this->getFieldData()->handleEditorMode($fieldSettings, 'cat_content');

        if (empty($fieldSettings)){
            $fieldSettings = (array)$category;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$category];
        }

        event()->dispatch($this->getPostData()->getOnPostCategoryDefaultField());
        $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getPostData()->getOnPostCategoryDefaultField()->getFieldSlug(), $fieldSettings);
        $fieldItems = $fieldForm->getHTMLFrag();

        view('Modules::Post/Views/Category/edit', [
            'Data' => $category,
            'FieldItems' => $fieldItems,
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

        $categoryToUpdate = $this->postData->createCategory();
        $categoryToUpdate['cat_slug'] = helper()->slug(input()->fromPost()->retrieve('cat_slug'));
        $this->postData->updateWithCondition($categoryToUpdate, ['cat_slug' => $slug], $this->postData->getCategoryTable());

        $slug = $categoryToUpdate['cat_slug'];
        session()->flash(['Post Category Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.edit', ['category' => $slug]));
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        view('Modules::Post/Views/Category/index');
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trash(string $slug)
    {
        $toUpdate = [
            'cat_status' => -1
        ];
        $this->postData->updateWithCondition($toUpdate, ['cat_slug' => $slug], $this->postData->getCategoryTable());
        session()->flash(['Category Moved To Trash'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.index'));
    }

    /**
     * @throws Exception
     */
    public function trashMultiple()
    {
        if (empty(input()->fromPost()->retrieve('itemsToTrash')) && !is_array(input()->fromPost()->retrieve('itemsToTrash'))){
            session()->flash(['Nothing To Trash'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('posts.index'));
        }
        $itemsToTrash = array_map(function ($item){
            $itemCopy = json_decode($item, true);
            $item = [];
            foreach ($itemCopy as $k => $v){
                if (key_exists($k, array_flip($this->postData->getCategoryColumns()))){
                    $item[$k] = $v;
                }
            }
            $item['cat_status'] = '-1';
            return $item;
        }, input()->fromPost()->retrieve('itemsToTrash'));

        try {
            db()->insertOnDuplicate(Tables::getTable(Tables::CATEGORIES), $itemsToTrash, ['cat_status']);
        } catch (Exception $e){
            session()->flash(['Fail To Trash Category Items']);
            redirect(route('posts.category.index'));
        }
        session()->flash(['Categories Trashed'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.index'));
    }

    /**
     * @param string $slug
     * @return void
     * @throws Exception
     */
    public function delete(string $slug): void
    {
        try {
            $this->getPostData()->deleteWithCondition(whereCondition: "cat_slug = ?", parameter: [$slug], table: $this->getPostData()->getCategoryTable());
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
                $category = $this->getPostData()
                    ->selectWithConditionFromCategory(['*'], "slug_id = ?", [$slugID]);
                if (isset($category->slug_id) && isset($category->cat_slug)){
                    return "/categories/$category->slug_id/$category->cat_slug";
                }
                return false;
            }, onSlugState: function ($slug){
            $category = $this->getPostData()
                ->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$slug]);
            if (isset($category->slug_id) && isset($category->cat_slug)){
                return "/categories/$category->slug_id/$category->cat_slug";
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