<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Controllers;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Rules\FieldValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class FieldController
{
    use UniqueSlug, Validator, FieldValidationRules;

    private FieldData $fieldData;

    public function __construct(FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {

        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::FIELD . '::' . 'field_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'field_id'],
            ['type' => 'text', 'slug' => Tables::FIELD . '::' . 'field_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'field_name'],
            ['type' => 'date_time_local', 'slug' => Tables::FIELD . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];



        $data = null;
        db(onGetDB: function (TonicsQuery $db) use (&$data){
            $table = Tables::getTable(Tables::FIELD);
            $tblCol = '*, CONCAT("/admin/tools/field/", field_slug, "/edit" ) as _edit_link, CONCAT("/admin/tools/field/items/", field_slug, "/builder") as _builder_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('field_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });
        
        view('Modules::Field/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_BUILDER',

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
        if ($this->getFieldData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getFieldData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getFieldData()->isDataTableType(AbstractDataLayer::DataTableEventTypeCopyFieldItems,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if (( $data = $this->copyFieldItemsJSON($entityBag))) {
                response()->onSuccess($data, "Copied Field Item(s)", more: AbstractDataLayer::DataTableEventTypeCopyFieldItems);
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
        view('Modules::Field/Views/create');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->fieldStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('fields.create'));
        }

        try {
            $widget = $this->getFieldData()->createField();
            $widgetReturning = null;
            db(onGetDB: function ($db) use ($widget, &$widgetReturning){
                $widgetReturning = $db->insertReturning($this->getFieldData()->getFieldTable(), $widget, $this->getFieldData()->getFieldColumns(), 'field_id');
            });

            $onFieldCreate = new OnFieldCreate($widgetReturning, $this->getFieldData());
            event()->dispatch($onFieldCreate);

            session()->flash(['Field Created'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('fields.edit', ['field' => $onFieldCreate->getFieldSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating The Field Item'], input()->fromPost()->all());
            redirect(route('widgets.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $menu = $this->getFieldData()->selectWithCondition($this->getFieldData()->getFieldTable(), ['*'], "field_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onWidgetCreate = new OnFieldCreate($menu, $this->getFieldData());
        view('Modules::Field/Views/edit', [
            'Data' => $onWidgetCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->fieldUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('fields.edit', [$slug]));
        }

        try {
            $widgetToUpdate = $this->getFieldData()->createField();
            $widgetToUpdate['field_slug'] = helper()->slug(input()->fromPost()->retrieve('field_slug'));
            db(onGetDB: function (TonicsQuery $db) use ($widgetToUpdate) {
                $db->FastUpdate($this->getFieldData()->getFieldTable(), $widgetToUpdate, db()->Where('field_slug', '=', $slug));
            });

            $slug = $widgetToUpdate['field_slug'];
            session()->flash(['Field Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('fields.edit', ['field' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The Field Item']);
            redirect(route('fields.edit', [$slug]));
        }
    }


    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getFieldData()->dataTableUpdateMultiple([
            'id' => 'field_id',
            'table' => Tables::getTable(Tables::FIELD),
            'rules' => $this->fieldUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getFieldData()->dataTableDeleteMultiple([
            'id' => 'field_id',
            'table' => Tables::getTable(Tables::FIELD),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool|array|null
     */
    public function copyFieldItemsJSON($entityBag): bool|array|null
    {
        try {
            $fieldIDS = [];
            $fieldItems = $this->getFieldData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveCopyFieldItems, $entityBag);
            foreach ($fieldItems as $fieldItem){
                if (isset($fieldItem->{"fields::field_id"})){
                    $fieldIDS[] = $fieldItem->{"fields::field_id"};
                }
            }

            $fields = null;
            // dd($fieldIDS);
            db(onGetDB: function (TonicsQuery $db) use ($fieldIDS, &$fields) {
                $fields = $db->Select("tf.field_name AS fk_field_id, tft.field_name AS field_name, tft.field_id AS field_id, field_parent_id, field_options")
                    ->From("{$this->getFieldData()->getFieldItemsTable()} tft")
                    ->Join("{$this->getFieldData()->getFieldTable()} tf", 'tf.field_id', 'tft.fk_field_id')
                    ->WhereIn('tf.field_id', $fieldIDS)->FetchResult();
            });

            return $fields;

        } catch (\Exception $exception) {
            // log..
        }

        return false;

    }

    /**
     * @throws \Exception
     */
    public function fieldResetItems(): void
    {
        $modules = [...helper()->getModuleActivators([ExtensionConfig::class, FieldItemsExtensionConfig::class]),
            ...helper()->getModuleActivators([ExtensionConfig::class, FieldItemsExtensionConfig::class], helper()->getAllAppsDirectory())];

        /** @var FieldItemsExtensionConfig&ExtensionConfig $module */
        try {
            apcu_clear_cache();
            foreach ($modules as $module){
                $fieldItems = $module->fieldItems();

                if (helper()->isJSON($fieldItems)){
                    $fieldItems = json_decode($fieldItems);
                }

                if (is_array($fieldItems)){
                    $this->getFieldData()->importFieldItems($fieldItems);
                }
            }
            session()->flash(['Field Items Reset Successful'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('fields.index'));
        } catch (\Exception $exception){
            // Log..
        }

        session()->flash(['Error Occurred Resetting Field Items']);
        redirect(route('fields.index'));
    }

    /**
     * @throws \Exception
     */
    public function getFieldItemsAPI(): void
    {
        $this->fieldData->getFieldItemsAPI();
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }


}
