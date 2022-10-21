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
            ['type' => '', 'title' => 'From', 'slug' => 'from', 'minmax' => '200px, 1fr', 'td' => 'from'],
            ['type' => 'text', 'title' => 'To', 'slug' => 'to', 'minmax' => '150px, 1fr', 'td' => 'to'],
            ['type' => 'select', 'title' => 'Type', 'slug' => 'type', 'select_data' => "301,302", 'minmax' => '50px, 1fr', 'td' => 'redirection_type'],
            ['type' => '', 'slug' => 'date_added', 'title' => 'Date Added', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
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
                response()->onError(500);
            }
        } elseif ($this->getDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
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
        try {
            $jsonValues = db()->Select('*')->From(Tables::getTable(Tables::GLOBAL))->WhereEquals('`key`', 'url_redirections')->FetchFirst();
            if (property_exists($jsonValues, 'value')){
                $jsonValues = json_decode($jsonValues->value);
                $deleteItems = $this->getDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
                # Remove
                foreach ($jsonValues as $jsonKey => $jsonValue){
                    foreach ($deleteItems as $deleteItemKey => $deleteItem){
                        if (isset($deleteItem->from)){
                            if ($jsonValue->from === $deleteItem->from && $jsonValue->date === $deleteItem->date_added){
                                unset($jsonValues[$jsonKey]);
                                unset($deleteItems[$deleteItemKey]);
                                break;
                            }
                        }
                    }
                }
                # Update JSON VALUES
                $jsonValues = array_values($jsonValues);
                $table = Tables::getTable(Tables::GLOBAL);
                db()->Update($table)
                    ->Set('value', json_encode($jsonValues, JSON_UNESCAPED_SLASHES))
                    ->WhereEquals('`key`', 'url_redirections')
                    ->FetchFirst();
                return true;
            }
        }catch (\Exception $exception){
            // Log..

        }
        return false;
    }

    protected function updateMultiple($entityBag)
    {
        try {
            $updateItems = $this->getDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            $jsonValues = db()->Select('*')->From(Tables::getTable(Tables::GLOBAL))->WhereEquals('`key`', 'url_redirections')->FetchFirst();
            if (property_exists($jsonValues, 'value')){
                $jsonValues = json_decode($jsonValues->value);

                # Update
                foreach ($jsonValues as $jsonValue){
                    foreach ($updateItems as $updateItemKey => $updateItem){
                        if (isset($updateItem->from)){
                            if ($jsonValue->from === $updateItem->from && $jsonValue->date === $updateItem->date_added){
                                $jsonValue->to = $updateItem->to;
                                $jsonValue->redirection_type = $updateItem->type ?? 301;
                                unset($updateItems[$updateItemKey]);
                                break;
                            }
                        }
                    }
                }
                # Update JSON VALUES
                $jsonValues = array_values($jsonValues);
                $table = Tables::getTable(Tables::GLOBAL);
                db()->Update($table)
                    ->Set('value', json_encode($jsonValues, JSON_UNESCAPED_SLASHES))
                    ->WhereEquals('`key`', 'url_redirections')
                    ->FetchFirst();
                return true;
            }
        } catch (\Exception $exception){
            // Log..
        }

        return false;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }
}