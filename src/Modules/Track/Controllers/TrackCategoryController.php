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

namespace App\Modules\Track\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\States\CommonResourceRedirection;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnTrackCategoryCreate;
use App\Modules\Track\Helper\TrackRedirection;
use App\Modules\Track\Rules\TrackValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class TrackCategoryController
{
    private TrackData $trackData;

    use Validator, TrackValidationRules, UniqueSlug;

    public function __construct(TrackData $trackData)
    {
        $this->trackData = $trackData;
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        $table = Tables::getTable(Tables::TRACK_CATEGORIES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::TRACK_CATEGORIES . '::' . 'track_cat_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'track_cat_id'],
            ['type' => 'text', 'slug' => Tables::TRACK_CATEGORIES . '::' . 'track_cat_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'track_cat_name'],
            ['type' => 'date_time_local', 'slug' => Tables::TRACK_CATEGORIES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data){
            $tblCol = '*, CONCAT("/admin/tracks/category/", track_cat_slug, "/edit" ) as _edit_link, CONCAT("/track_categories/", track_cat_slug) as _preview_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('status'),
                    function (TonicsQuery $db) {
                        $db->WhereEquals('track_cat_status', url()->getParam('status'));
                    },
                    function (TonicsQuery $db) {
                        $db->WhereEquals('track_cat_status', 1);

                    })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('track_cat_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Track/Views/Category/index', [
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
        if ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        event()->dispatch($this->getTrackData()->getOnTrackCategoryDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Track/Views/Category/create', [
            'Categories' => $this->getTrackData()->getCategoryHTMLSelect(),
            'FieldItems' => $this->getFieldData()->generateFieldWithFieldSlug($this->getTrackData()->getOnTrackCategoryDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
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

        if (input()->fromPost()->hasValue('track_cat_slug') === false){
            $_POST['track_cat_slug'] = helper()->slug(input()->fromPost()->retrieve('track_cat_name'));
        }

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->trackCategoryStoreRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.category.create'));
        }

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $dbTx = db();
        try {
            $dbTx->beginTransaction();
            $category = $this->trackData->createCategory();
            $td = $this->getTrackData();
            $categoryReturning = null;
            db(onGetDB: function ($db) use ($category, $td, &$categoryReturning){
                $categoryReturning = $db->insertReturning($td::getTrackCategoryTable(), $category, $td->getTrackCategoryColumns(), 'track_cat_id');
            });

            $onTrackCategoryCreate = new OnTrackCategoryCreate($categoryReturning, $td);
            event()->dispatch($onTrackCategoryCreate);
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();

            apcu_clear_cache();
            session()->flash(['Track Category Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tracks.category.edit', ['category' => $onTrackCategoryCreate->getCatSlug()]));
        }catch (Exception $exception){
            // Log..
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            session()->flash(['An Error Occurred, Creating Track Category'], input()->fromPost()->all());
            redirect(route('tracks.category.create'));
        }

    }

    /**
     * @param string $slug
     * @throws \Exception
     */
    public function edit(string $slug): void
    {
        $category = null;
        db(onGetDB: function ($db) use ($slug, &$category){
            $category = $db->Select('*, CONCAT("/track_categories/", track_cat_slug) as _preview_link')
                ->From($this->getTrackData()::getTrackCategoryTable())->WhereEquals('track_cat_slug', $slug)->FetchFirst();
        });

        if (!is_object($category)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($category->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$category;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$category];
        }

        event()->dispatch($this->getTrackData()->getOnTrackCategoryDefaultField());
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getTrackData()->getOnTrackCategoryDefaultField()->getFieldSlug(), $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $category);
        }

        view('Modules::Track/Views/Category/edit', [
            'FieldItems' => $htmlFrag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug): void
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->trackCategoryUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('tracks.category.edit', [$slug]));
        }

        if (input()->fromPost()->hasValue('track_cat_parent_id') && input()->fromPost()->hasValue('track_cat_id')){
            $trackCatParentID = input()->fromPost()->retrieve('track_cat_parent_id');
            $trackCatID = input()->fromPost()->retrieve('track_cat_id');
            $category = null;
            db(onGetDB: function ($db) use ($slug, &$category){
                $category = $db->Select('*')->From($this->getTrackData()::getTrackCategoryTable())->WhereEquals('track_cat_slug', $slug)->FetchFirst();
            });

            // Track Category Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            if ($trackCatParentID === $trackCatID){
                $_POST['track_cat_parent_id'] = $category->track_cat_parent_id;
                // Log..
                // Error Message is: Track Category Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            }
        }

        $categoryToUpdate = $this->trackData->createCategory();
        $categoryToUpdate['track_cat_slug'] = helper()->slug(input()->fromPost()->retrieve('track_cat_slug'));
        db(onGetDB: function ($db) use ($slug, $categoryToUpdate) {
            $db->FastUpdate($this->trackData::getTrackCategoryTable(), $categoryToUpdate, db()->Where('track_cat_slug', '=', $slug));
        });

        $slug = $categoryToUpdate['track_cat_slug'];

        apcu_clear_cache();
        if (input()->fromPost()->has('_fieldErrorEmitted') === true){
            session()->flash(['Post Category Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
        } else {
            session()->flash(['Track Category Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        }
        redirect(route('tracks.category.edit', ['category' => $slug]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableUpdateMultiple([
            'id' => 'track_cat_id',
            'table' => Tables::getTable(Tables::TRACK_CATEGORIES),
            'rules' => $this->trackCategoryUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableDeleteMultiple([
            'id' => 'track_cat_id',
            'table' => Tables::getTable(Tables::TRACK_CATEGORIES),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID){
                $category = null;
                db(onGetDB: function ($db) use ($slugID, &$category){
                    $category = $db->Select('*')->From($this->getTrackData()::getTrackCategoryTable())
                        ->WhereEquals('slug_id', $slugID)->FetchFirst();
                });

                if (isset($category->slug_id) && isset($category->track_cat_slug)){
                    return TrackRedirection::getTrackCategoryAbsoluteURLPath((array)$category);
                }
                return false;
            }, onSlugState: function ($slug){
            $category = null;
            db(onGetDB: function ($db) use ($slug, &$category){
                $category = $db->Select('*')->From($this->getTrackData()::getTrackCategoryTable())
                    ->WhereEquals('track_cat_slug', $slug)->FetchFirst();
            });
            if (isset($category->slug_id) && isset($category->track_cat_slug)){
                return TrackRedirection::getTrackCategoryAbsoluteURLPath((array)$category);
            }
            return false;
        });

        $redirection->runStates();
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->getTrackData()->getFieldData();
    }

}