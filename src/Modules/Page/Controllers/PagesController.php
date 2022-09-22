<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Page\Data\PageData;
use App\Modules\Page\Events\BeforePageView;
use App\Modules\Page\Events\OnPageCreated;
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Page\Rules\PageValidationRules;
use App\Modules\Post\Events\OnPostUpdate;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
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
        $pageTbl = Tables::getTable(Tables::PAGES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'page_id', 'title' => 'Page ID', 'minmax' => '50px, .5fr', 'td' => 'page_id'],
            ['type' => 'text', 'slug' => Tables::PAGES . '::' . 'page_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'page_title'],
            ['type' => 'date_time_local', 'slug' => Tables::PAGES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = '*, CONCAT("/admin/pages/", page_id, "/edit" ) as _edit_link, page_slug as _preview_link';

        $pageData = db()->Select($tblCol)
            ->From($pageTbl)
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('page_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('page_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('page_title', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($pageTbl) {
                $db->WhereBetween(table()->pickTable($pageTbl, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($pageTbl, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));


        view('Modules::Page/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'postData' => $pageData ?? [],
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
        if ($this->getPageData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getPageData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        $this->fieldData->getFieldItemsAPI();

        event()->dispatch($this->onPageDefaultField);

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Page/Views/create', [
            'FieldSelection' => $this->fieldData->getFieldsSelection($this->onPageDefaultField->getFieldSlug()),
            'FieldItems' => $this->fieldData->generateFieldWithFieldSlug($this->onPageDefaultField->getFieldSlug(), $oldFormInput)->getHTMLFrag(),
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
        $pageReturning = db()->insertReturning($this->getPageData()->getPageTable(), $page, $this->getPageData()->getPageColumns(), 'page_id');

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
        $onPageDefaultField->setFieldSlug($fieldIDS);
        event()->dispatch($onPageDefaultField);

        $fieldItems = $this->fieldData->generateFieldWithFieldSlug($onPageDefaultField->getFieldSlug(), $fieldSettings)->getHTMLFrag();
        view('Modules::Page/Views/edit', [
            'Data' => $page,
            'FieldSelection' => $this->fieldData->getFieldsSelection($onPageDefaultField->getFieldSlug()),
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
        if (input()->fromPost()->has('page_status') === false) {
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
            db()->FastUpdate($this->pageData->getPageTable(), $pageToUpdate, db()->Where('page_id', '=', $id));
        } catch (Exception) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('pages.edit', [$id]));
        }

        session()->flash(['Page Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.edit', ['page' => $id]));
    }


    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        $pageTable = Tables::getTable(Tables::PAGES);
        try {
            $updateItems = $this->getPageData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            db()->beginTransaction();
            foreach ($updateItems as $updateItem) {
                $db = db();
                $updateChanges = [];
                $colForEvent = [];
                foreach ($updateItem as $col => $value) {
                    $tblCol = $this->getPageData()->validateTableColumnForDataTable($col);

                    # We get the column (this also validates the table)
                    $setCol = table()->getColumn(Tables::getTable($tblCol[0]), $tblCol[1]);

                    $colForEvent[$tblCol[1]] = $value;
                    $updateChanges[$setCol] = $value;
                }

                # Validate The col and type
                $validator = $this->getValidator()->make($colForEvent, $this->pageUpdateMultipleRule());
                if ($validator->fails()) {
                    throw new \Exception("DataTable::Validation Error {$validator->errorsAsString()}");
                }

                $pageID = $updateChanges[table()->getColumn($pageTable, 'page_id')];
                $db->FastUpdate($pageTable, $updateChanges, db()->Where('page_id', '=', $pageID));
            }
            db()->commit();
            apcu_clear_cache();
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
    public function deleteMultiple($entityBag): bool
    {
        $toDelete = [];
        try {
            $deleteItems = $this->getPageData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
            foreach ($deleteItems as $deleteItem) {
                foreach ($deleteItem as $col => $value) {
                    $tblCol = $this->getPageData()->validateTableColumnForDataTable($col);
                    if ($tblCol[1] === 'page_id') {
                        $toDelete[] = $value;
                    }
                }
            }

            db()->FastDelete(Tables::getTable(Tables::PAGES), db()->WhereIn('page_id', $toDelete));
            return true;
        } catch (\Exception $exception) {
            // log..
            return false;
        }
    }



    /**
     * @throws Exception
     */
    public function viewPage()
    {
        $foundURL = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode();
        $page = $foundURL->getMoreSettings('GET');
        if (!is_object($page)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($page->field_settings, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$page;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$page];
        }

        $pagePath = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode()?->getFullRoutePath();
        /** @var $beforePageViewEvent BeforePageView */
        $beforePageViewEvent = event()->dispatch(new BeforePageView($fieldSettings, $pagePath));

        $onFieldUserForm = new OnFieldFormHelper([], new FieldData());
        $fieldSettings = $beforePageViewEvent->getFieldSettings();
        $fieldSlugs = $this->getFieldSlug($beforePageViewEvent->getFieldSettings());
        $onFieldUserForm->handleFrontEnd($fieldSlugs, $fieldSettings, $beforePageViewEvent->isCacheData());

        view($beforePageViewEvent->getViewName());
    }

    /**
     * @throws \Exception
     */
    public function getFieldSlug($page): array
    {
        $slug = $page['field_ids'];
        $fieldSlugs = json_decode($slug) ?? [];
        if (is_object($fieldSlugs)) {
            $fieldSlugs = (array)$fieldSlugs;
        }

        if (empty($fieldSlugs) || !is_array($fieldSlugs)) {
            // return default fields
            return ["default-page-field", "post-home-page"];
        }

        $hiddenSlug = event()->dispatch(new OnPageDefaultField())->getHiddenFieldSlug();
        return [...$fieldSlugs, ...$hiddenSlug];
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
