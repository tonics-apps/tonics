<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
use App\Modules\Page\Events\OnPageTemplate;
use App\Modules\Page\Rules\PageValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class PagesController
{
    use Validator, PageValidationRules;

    private PageData            $pageData;
    private ?FieldData          $fieldData;
    private ?OnPageDefaultField $onPageDefaultField;

    public function __construct (PageData $pageData, FieldData $fieldData = null, OnPageDefaultField $onPageDefaultField = null)
    {
        $this->pageData = $pageData;
        $this->fieldData = $fieldData;
        $this->onPageDefaultField = $onPageDefaultField;
    }

    /**
     * @throws \Exception
     */
    public function index (): void
    {
        $onPageTemplateEvent = new OnPageTemplate();
        event()->dispatch($onPageTemplateEvent);
        $templateNames = $onPageTemplateEvent->getTemplateNames();
        $pageTemplateSelectDataAttribute = '';
        foreach ($templateNames as $templateName => $templateClass) {
            $pageTemplateSelectDataAttribute .= $templateName . ',';
        }

        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'page_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'page_id'],
            ['type' => 'text', 'slug' => Tables::PAGES . '::' . 'page_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'page_title'],
            ['type' => 'select', 'slug' => Tables::PAGES . '::' . 'page_template', 'title' => 'Template', 'minmax' => '150px, 1.6fr', 'select_data' => "$pageTemplateSelectDataAttribute", 'td' => 'page_template'],
            ['type' => 'date_time_local', 'slug' => Tables::PAGES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function (TonicsQuery $db) use (&$data) {
            $tblCol = '*, CONCAT("/admin/pages/", page_id, "/edit" ) as _edit_link, page_slug as _preview_link';
            $table = Tables::getTable(Tables::PAGES);
            $data = $db->Select($tblCol)
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
        });

        view('Modules::Page/Views/index', [
            'DataTable' => [
                'headers'       => $dataTableHeaders,
                'paginateData'  => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable (): void
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
    public function create ()
    {
        $this->fieldData->getFieldItemsAPI();

        event()->dispatch($this->onPageDefaultField);

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Page/Views/create', [
            'FieldSelection' => $this->fieldData->getFieldsSelection($this->onPageDefaultField->getFieldSlug()),
            'FieldItems'     => $this->fieldData->generateFieldWithFieldSlug($this->onPageDefaultField->getFieldSlug(), $oldFormInput)->getHTMLFrag(),
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function store ()
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
        $pageReturning = null;
        db(onGetDB: function ($db) use ($page, &$pageReturning) {
            $pageReturning = $db->insertReturning($this->getPageData()->getPageTable(), $page, $this->getPageData()->getPageColumns(), 'page_id');
        });

        $onPageCreated = new OnPageCreated($pageReturning, $this->pageData);
        event()->dispatch($onPageCreated);

        session()->flash(['Page Created'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('pages.edit', ['page' => $onPageCreated->getPageID()]));
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws Exception
     */
    public function edit (string $id)
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

        if (isset($fieldSettings['_fieldDetails'])) {
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']), $onPageDefaultField->getFieldSlug());
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $htmlFrag = $this->fieldData->generateFieldWithFieldSlug($onPageDefaultField->getFieldSlug(), $fieldSettings)->getHTMLFrag();
            addToGlobalVariable('Data', $page);
        }

        view('Modules::Page/Views/edit', [
            'FieldSelection' => $this->fieldData->getFieldsSelection($onPageDefaultField->getFieldSlug()),
            'FieldItems'     => $htmlFrag,
        ]);
    }


    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update (string $id)
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
            $pageToUpdate['page_slug'] = helper()->slugForPage(input()->fromPost()->retrieve('page_slug'), '-');

            db(onGetDB: function ($db) use ($id, $pageToUpdate) {
                $db->FastUpdate($this->pageData->getPageTable(), $pageToUpdate, db()->Where('page_id', '=', $id));
            });

        } catch (Exception) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('pages.edit', [$id]));
        }

        # For Fields
        apcu_clear_cache();
        if (input()->fromPost()->has('_fieldErrorEmitted') === true) {
            session()->flash(['Page Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('pages.edit', [$id]));
        } else {
            session()->flash(['Page Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('pages.edit', ['page' => $id]));
        }

    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple ($entityBag): bool
    {
        return $this->getPageData()->dataTableUpdateMultiple([
            'id'        => 'page_id',
            'table'     => Tables::getTable(Tables::PAGES),
            'rules'     => $this->pageUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws Exception
     */
    public function deleteMultiple ($entityBag): bool
    {
        return $this->getPageData()->dataTableDeleteMultiple([
            'id'        => 'page_id',
            'table'     => Tables::getTable(Tables::PAGES),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @throws Exception
     */
    public function viewPage (): void
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

        $onPageTemplateEvent = new OnPageTemplate();
        $onPageTemplateEvent->setFieldSettings($fieldSettings);
        event()->dispatch($onPageTemplateEvent);
        if (isset($fieldSettings['page_template']) && $onPageTemplateEvent->exist($fieldSettings['page_template'])) {
            $pageTemplateObject = $onPageTemplateEvent->getTemplate($fieldSettings['page_template']);
            $pageTemplateObject->handleTemplate($onPageTemplateEvent);

            $fieldSettings = $onPageTemplateEvent->getFieldSettings();
            $pagePath = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode()?->getFullRoutePath();
            /** @var $beforePageViewEvent BeforePageView */
            $beforePageViewEvent = event()->dispatch(new BeforePageView($fieldSettings, $pagePath));
            $fieldSettings = $beforePageViewEvent->getFieldSettings();

            $onFieldUserForm = new OnFieldFormHelper([], new FieldData());
            $fieldSlugs = $this->getFieldSlug($fieldSettings);

            $onFieldUserForm->handleFrontEnd($fieldSlugs, $fieldSettings);
            view($onPageTemplateEvent->getViewName());
            return;
        }

        die('No Valid Page Template');
    }

    /**
     * @throws \Exception
     */
    public function getFieldSlug ($page): array
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
    public function getPageData (): PageData
    {
        return $this->pageData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData (): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return OnPageDefaultField|null
     */
    public function getOnPageDefaultField (): ?OnPageDefaultField
    {
        return $this->onPageDefaultField;
    }
}
