<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\Tonics404Handler\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class Tonics404HandlerController
{
    private AbstractDataLayer $dataLayer;
    private ?FieldData $fieldData;

    CONST TONICS404HANDLER_FIELD_SLUG = 'app-tonics404handler-settings';

    /**
     * @param AbstractDataLayer $dataLayer
     * @param FieldData|null $fieldData
     */
    public function __construct(AbstractDataLayer $dataLayer, FieldData $fieldData = null)
    {
        $this->dataLayer = $dataLayer;
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::BROKEN_LINKS . '::' . 'id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'id'],
            ['type' => '', 'title' => 'From', 'slug' => Tables::BROKEN_LINKS . '::' . 'from', 'minmax' => '200px, 1fr', 'td' => 'from'],
            ['type' => 'text', 'title' => 'To', 'slug' => Tables::BROKEN_LINKS . '::' . 'to', 'minmax' => '150px, 1fr', 'td' => 'to'],
            ['type' => 'select', 'title' => 'Redirection Type', 'slug' => Tables::BROKEN_LINKS . '::' . 'redirection_type', 'select_data' => "301,302", 'minmax' => '50px, 1fr', 'td' => 'redirection_type'],
            ['type' => '', 'slug' => Tables::BROKEN_LINKS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $table = Tables::getTable(Tables::BROKEN_LINKS);
        $data = db()->Select('*')
            ->From($table)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('`from`', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            [self::TONICS404HANDLER_FIELD_SLUG],
        )->getHTMLFrag();

        view('Apps::Tonics404Handler/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' =>  $data ?? [],
                'dataTableType' => 'Tonics404Handler_VIEW',
            ],
            'FieldItems' => $fieldItems,
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500, 'Error Occur Deleting Records');
            }
        } elseif ($this->getDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'Error Occur Updating Records');
            }
        }

        # New Insert...
        if (isset($_POST['_fieldDetails'])){
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($_POST['_fieldDetails']));
            if (isset($fieldCategories[self::TONICS404HANDLER_FIELD_SLUG])){
                $fieldsItems = $fieldCategories[self::TONICS404HANDLER_FIELD_SLUG];
                $toInsert = [];
                $fromName = 'tonics404handler_404_url';
                $redirectToName = 'tonics404handler_redirect_to';
                foreach ($fieldsItems as $fieldsItem){
                    if (isset($fieldsItem->_children)){
                        $settings = [
                            'from' => '',
                            'to'   => '',
                        ];
                        foreach ($fieldsItem->_children as $child){
                            if (isset($child->field_data)){
                                if ($child->field_input_name === $fromName){ $settings['from'] = $child->field_data[$fromName]; }
                                if ($child->field_input_name === $redirectToName){ $settings['to'] = $child->field_data[$redirectToName]; }
                            }
                        }
                        $toInsert[] = $settings;
                    }
                }

                try {
                    db()->InsertOnDuplicate(
                        Tables::getTable(Tables::BROKEN_LINKS),
                        $toInsert,
                        ['to']
                    );

                    session()->flash(['Redirect Added or Updated'], type: Session::SessionCategories_FlashMessageSuccess);
                    redirect(route('tonics404Handler.settings'));
                } catch (\Exception $exception){
                    // log..
                }

            }
        }

        session()->flash(['Error Occur Inserting New Redirect']);
        redirect(route('tonics404Handler.settings'));
    }

    /**
     * @return AbstractDataLayer
     */
    public function getDataLayer(): AbstractDataLayer
    {
        return $this->dataLayer;
    }

    /**
     * @param $entityBag
     * @return bool
     */
    protected function deleteMultiple($entityBag): bool
    {
        return $this->getFieldData()->dataTableDeleteMultiple('id', Tables::getTable(Tables::BROKEN_LINKS), $entityBag);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        $rulesUpdate = [
            'id' => ['numeric'],
            'updated_at' => ['required', 'string'],
            'redirection_type' => ['required', 'numeric'],
        ];
        return $this->getFieldData()->dataTableUpdateMultiple('id', Tables::getTable(Tables::BROKEN_LINKS), $entityBag, $rulesUpdate);
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }
}