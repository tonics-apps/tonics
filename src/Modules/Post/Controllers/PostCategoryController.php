<?php

namespace App\Modules\Post\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCategoryCreate;
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
        view('Modules::Post/Views/Category/create', [
            'Categories' => $this->getPostData()->getCategoryHTMLSelect(),
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postCategoryStoreRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('posts.category.create'));
        }

        $category = $this->postData->createCategory();
        $categoryReturning = $this->postData->insertForPost($category, PostData::Category_INT);

        $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning);
        event()->dispatch($onPostCategoryCreate);

        session()->flash(['Post Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('posts.category.edit', ['category' => $onPostCategoryCreate->getCatSlug()]));
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function storeFromImport(array $categoryData): bool
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
            dd($e->getMessage());
            helper()->sendMsg('PostCategoryController::storeFromImport()', $e->getMessage(), 'issue');
            return false;
        }
        $onPostCategoryCreate = new OnPostCategoryCreate($categoryReturning);
        event()->dispatch($onPostCategoryCreate);
        $_POST = $previousPOSTGlobal;
        return true;

    }

    /**
     * @param string $slug
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $category = $this->postData->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$slug]);

        if (!is_object($category)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $postCategoryCreate = new OnPostCategoryCreate($category);
        $categoryCurrent = new \stdClass();
        $categoryCurrent->{'cat_parent_id'} = $postCategoryCreate->getCatParentID();
        view('Modules::Post/Views/Category/edit', [
            'Categories' => $this->getPostData()->getCategoryHTMLSelect($categoryCurrent),
            'SiteURL' => AppConfig::getAppUrl(),
            'CatStatus' => $this->postData->getPostStatusHTMLFrag($postCategoryCreate->getCatStatus()),
            'Data' => $postCategoryCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->postCategoryUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('posts.category.edit', [$slug]));
        }

        $categoryToUpdate = $this->postData->createCategory();
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
        $data = $this->getPostData()->generatePaginationData(
            $this->getPostData()->getCategoryPaginationColumns(),
            'cat_name',
            $this->getPostData()->getCategoryTable());

        $categoryListing = '';
        if ($data !== null){
            $categoryListing = $this->postData->adminCategoryListing($data->data);
            unset($data->data);
        }

        view('Modules::Post/Views/Category/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'CategoryListing' => $categoryListing,
        ]);

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

    public function delete(string $slug)
    {
        dd($slug);
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }


}