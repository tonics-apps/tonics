<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Page\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Page\Data\PageData;
use App\Modules\Page\Events\OnPageCreated;
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Page\Rules\PageValidationRules;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class PagesController
{
    use Validator, PageValidationRules;

    private PageData $pageData;
    private ?FieldData $fieldData;
    private ?OnPageDefaultField $onPageDefaultField;

    public function __construct(PageData $pageData, FieldData $fieldData = null, OnPageDefaultField $onPageDefaultField = null)
    {
        $this->pageData = $pageData;
        $this->fieldData = $fieldData;
        $this->onPageDefaultField = $onPageDefaultField;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $cols = '`page_id`, `page_title`, `page_slug`, `page_status`';
        $data = $this->getPageData()->generatePaginationData(
            $cols,
            'page_title',
            $this->getPageData()->getPageTable());

        $pageListing = '';
        if ($data !== null) {
            $pageListing = $this->getPageData()->adminPageListing($data->data, $this->pageData->getPageStatus());
            unset($data->data);
        }

        view('Modules::Page/Views/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'PageListing' => $pageListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        $this->fieldData->getFieldItemsAPI();

        event()->dispatch($this->onPageDefaultField);

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true);
        $oldFormInput = json_decode($oldFormInput, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Page/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldSelection' => $this->fieldData->getFieldsSelection($this->onPageDefaultField->getPostDefaultFieldSlug()),
            'FieldItems' => $this->fieldData->generateFieldWithFieldSlug($this->onPageDefaultField->getPostDefaultFieldSlug(), $oldFormInput)->getHTMLFrag(),
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        if (input()->fromPost()->hasValue('created_at') === false) {
            $_POST['created_at'] = helper()->date();
        }
        if (input()->fromPost()->hasValue('page_slug') === false) {
            $_POST['page_slug'] = helper()->slug(input()->fromPost()->retrieve('page_title'));
        }
        if (input()->fromPost()->hasValue('page_status') === false) {
            $_POST['page_status'] = "1";
        }
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->pageStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('pages.create'));
        }

        $page = $this->pageData->createPage(['token']);
        $pageReturning = db()->insertReturning($this->getPageData()->getPageTable(), $page, $this->getPageData()->getPageColumns());

        $onPageCreated = new OnPageCreated($pageReturning, $this->pageData);
        event()->dispatch($onPageCreated);

        session()->flash(['Page Created'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.edit', ['page' => $onPageCreated->getPageID()]));
    }

    /**
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function edit(string $id)
    {
        $this->fieldData->getFieldItemsAPI();

        $page = $this->pageData->selectWithCondition($this->pageData->getPageTable(), ['*'], "page_id = ?", [$id]);
        if (!is_object($page)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($page->field_settings, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$page;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$page];
        }

        $onPageDefaultField = $this->onPageDefaultField;
        $fieldIDS = ($page->field_ids === null) ? [] : json_decode($page->field_ids, true);
        $onPageDefaultField->setPostDefaultFieldSlug($fieldIDS);
        event()->dispatch($onPageDefaultField);
        $fieldItems = $this->fieldData->generateFieldWithFieldSlug($onPageDefaultField->getPostDefaultFieldSlug(), $fieldSettings)->getHTMLFrag();
        view('Modules::Page/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'Data' => $page,
            'FieldSelection' => $this->fieldData->getFieldsSelection($onPageDefaultField->getPostDefaultFieldSlug()),
            'FieldItems' => $fieldItems
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $id)
    {
        if (input()->fromPost()->hasValue('created_at') === false) {
            $_POST['created_at'] = helper()->date();
        }
        if (input()->fromPost()->hasValue('page_status') === false) {
            $_POST['page_status'] = "1";
        }
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->pageUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('pages.edit', [$id]));
        }

        try {
            $pageToUpdate = $this->pageData->createPage(['token']);
            $pageToUpdate['page_slug'] = helper()->slugForPage(input()->fromPost()->retrieve('page_slug'));
            $this->pageData->updateWithCondition($pageToUpdate, ['page_id' => $id], $this->pageData->getPageTable());
        } catch (Exception) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('pages.edit', [$id]));
        }

        session()->flash(['Page Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.edit', ['page' => $id]));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trash(string $id)
    {
        $toUpdate = [
            'page_status' => -1
        ];
        $this->pageData->updateWithCondition($toUpdate, ['page_id' => $id], $this->pageData->getPageTable());
        session()->flash(['Page Moved To Trash'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trashMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToTrash')) {
            session()->flash(['Nothing To Trash'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('pages.index'));
        }
        $itemsToTrash = array_map(function ($item) {
            $itemCopy = json_decode($item, true);
            $item = [];
            foreach ($itemCopy as $k => $v) {
                if (key_exists($k, array_flip($this->pageData->getPageColumns()))) {
                    $item[$k] = $v;
                }
            }
            $item['page_status'] = '-1';
            return $item;
        }, input()->fromPost()->retrieve('itemsToTrash'));

        try {
            db()->insertOnDuplicate(Tables::getTable(Tables::PAGES), $itemsToTrash, ['page_status']);
        } catch (\Exception $e) {
            session()->flash(['Fail To Trash Page Items']);
            redirect(route('pages.index'));
        }
        session()->flash(['Page(s) Trashed'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.index'));
    }

    /**
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function delete(string $id)
    {
        try {
            $this->getPageData()->deleteWithCondition(whereCondition: "page_id = ?", parameter: [$id], table: $this->getPageData()->getPageTable());
            session()->flash(['Page Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('pages.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                default:
                    session()->flash(['Failed To Delete Page']);
                    break;
            }
            apcu_clear_cache();
            redirect(route('pages.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('pages.index'));
        }

        $this->getPageData()->deleteMultiple(
            $this->getPageData()->getPageTable(),
            array_flip($this->getPageData()->getPageColumns()),
            'page_id',
            onSuccess: function (){
                session()->flash(['Page Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('pages.index'));
            },
            onError: function ($e){
                $errorCode = $e->getCode();
                switch ($errorCode){
                    default:
                        session()->flash(['Failed To Delete Page']);
                        break;
                }
                apcu_clear_cache();
                redirect(route('pages.index'));
            },
        );
    }

    /**
     * @return PageData
     */
    public function getPageData(): PageData
    {
        return $this->pageData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return OnPageDefaultField|null
     */
    public function getOnPageDefaultField(): ?OnPageDefaultField
    {
        return $this->onPageDefaultField;
    }
}
