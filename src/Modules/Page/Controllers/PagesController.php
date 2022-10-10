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
use App\Modules\Field\Events\OnFieldMetaBox;
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
    public function index(): void
    {
        $table = Tables::getTable(Tables::PAGES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'page_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'page_id'],
            ['type' => 'text', 'slug' => Tables::PAGES . '::' . 'page_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'page_title'],
            ['type' => 'date_time_local', 'slug' => Tables::PAGES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = '*, CONCAT("/admin/pages/", page_id, "/edit" ) as _edit_link, page_slug as _preview_link';

        $data = db()->Select($tblCol)
            ->From($table)
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('page_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('page_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('page_title', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));


        view('Modules::Page/Views/index', [
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

       // dd(json_decode($fieldSettings['_fieldDetails']));

        $onPageDefaultField = $this->onPageDefaultField;
        $fieldIDS = ($page->field_ids === null) ? [] : json_decode($page->field_ids, true);
        $onPageDefaultField->setFieldSlug($fieldIDS);
        event()->dispatch($onPageDefaultField);
        $fieldMainSlugs = array_combine($onPageDefaultField->getFieldSlug(), $onPageDefaultField->getFieldSlug());

        $fieldTable = $this->getFieldData()->getFieldTable();
        $fieldItemsTable = $this->getFieldData()->getFieldItemsTable();
        $fieldAndFieldItemsCols = $this->getFieldData()->getFieldAndFieldItemsCols();

        $originalFieldIDAndSlugs = db()->Select("field_id, field_slug")->From($this->getFieldData()->getFieldTable())
            ->WhereIn('field_slug', $onPageDefaultField->getFieldSlug())->OrderBy('field_id')->FetchResult();

        # For Field Items
        $fieldIDS = []; $categoriesFromFieldIDAndSlug = [];
        foreach ($originalFieldIDAndSlugs as $originalFieldIDAndSlug) {
            if (key_exists($originalFieldIDAndSlug->field_slug, $fieldMainSlugs)){
                $fieldIDS[] = $originalFieldIDAndSlug->field_id;
                $categoriesFromFieldIDAndSlug[$originalFieldIDAndSlug->field_slug] = [];
            }
        }

        $originalFieldItems = db()->Select($fieldAndFieldItemsCols)
            ->From($fieldItemsTable)
            ->Join($fieldTable, "$fieldTable.field_id", "$fieldItemsTable.fk_field_id")
            ->WhereIn('fk_field_id', $fieldIDS)->OrderBy('fk_field_id')->FetchResult();

        $buildHashes = [];
        foreach ($originalFieldItems as $originalFieldItem){
            $fieldOption = json_decode($originalFieldItem->field_options);
            $hash = $fieldOption->field_slug_unique_hash . '_' . $fieldOption->inputName;
            $originalFieldItem->field_options = $fieldOption;
            $buildHashes[$hash] = $originalFieldItem;
        }

        $fieldItems = json_decode($fieldSettings['_fieldDetails']);

        $fieldItems = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldItems, onData: function ($field) use ($buildHashes) {
            if (isset($field->field_options) && helper()->isJSON($field->field_options)){
                 $fieldOption = json_decode($field->field_options);
                 $hash = $fieldOption->field_slug_unique_hash . '_' . $fieldOption->field_input_name;
                if (key_exists($hash, $buildHashes)){
                    $field->field_options = json_decode(json_encode($buildHashes[$hash]->field_options));
                }
                $field->field_data = (array)$fieldOption;
                $field->field_options->{"_field"} = $field;
            }
            return $field;
        });
        $fieldCategories = [];
        foreach ($fieldItems as $fieldItem) {
            if (isset($fieldItem->main_field_slug) && key_exists($fieldItem->main_field_slug, $categoriesFromFieldIDAndSlug)){
                $fieldCategories[$fieldItem->main_field_slug][] = $fieldItem;
            }
        }

        // Sort and Arrange OriginalFieldItems
        $originalFieldCategories = [];
        $originalFieldItems = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $originalFieldItems);
        foreach ($originalFieldItems as $originalFieldItem) {
            if (isset($originalFieldItem->main_field_slug) && key_exists($originalFieldItem->main_field_slug, $categoriesFromFieldIDAndSlug)){
                $originalFieldCategories[$originalFieldItem->main_field_slug][] = $originalFieldItem;
            }
        }

        dd($fieldCategories, $originalFieldCategories);


        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();
        $htmlFrag = '';
        foreach ($fieldItems as $fieldItem) {
           $htmlFrag .= $onFieldMetaBox->getUsersForm($fieldItem->field_options->field_slug, $fieldItem->field_options);
        }

        // $htmlFrag = $this->fieldData->generateFieldWithFieldSlug($onPageDefaultField->getFieldSlug(), $fieldSettings)->getHTMLFrag();
        view('Modules::Page/Views/edit', [
            'Data' => $page,
            'FieldSelection' => $this->fieldData->getFieldsSelection($onPageDefaultField->getFieldSlug()),
            'FieldItems' => $htmlFrag
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
        return $this->getPageData()->dataTableUpdateMultiple('page_id', Tables::getTable(Tables::PAGES), $entityBag, $this->pageUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getPageData()->dataTableDeleteMultiple('page_id', Tables::getTable(Tables::PAGES), $entityBag);
    }

    /**
     * @throws Exception
     */
    public function viewPage(): void
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

        // dd($page, $fieldSettings, getGlobalVariableData());

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
