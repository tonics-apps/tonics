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

namespace App\Modules\Widget\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Widget\Data\WidgetData;
use App\Modules\Widget\Events\OnWidgetCreate;
use App\Modules\Widget\Rules\WidgetValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class WidgetController
{
    use UniqueSlug, Validator, WidgetValidationRules;

    private WidgetData $widgetData;

    public function __construct(WidgetData $widgetData)
    {
        $this->widgetData = $widgetData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $table = Tables::getTable(Tables::WIDGETS);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::WIDGETS . '::' . 'widget_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'widget_id'],
            ['type' => 'text', 'slug' => Tables::WIDGETS . '::' . 'widget_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'widget_name'],
            ['type' => 'date_time_local', 'slug' => Tables::WIDGETS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data){
            $tblCol = '*, CONCAT("/admin/tools/widget/", widget_slug, "/edit" ) as _edit_link, CONCAT("/admin/tools/widget/items/", widget_slug, "/builder") as _builder_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('widget_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Widget/Views/index', [
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
        if ($this->getWidgetData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getWidgetData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        view('Modules::Widget/Views/create');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->widgetStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('widgets.create'));
        }

        try {
            $widget = $this->getWidgetData()->createWidget();
            $widgetReturning = null;
            db(onGetDB: function ($db) use ($widget, &$widgetReturning){
                $widgetReturning = $db->insertReturning($this->getWidgetData()->getWidgetTable(), $widget, $this->getWidgetData()->getWidgetColumns(), 'widget_id');
            });
            $onWidgetCreate = new OnWidgetCreate($widgetReturning, $this->getWidgetData());
            event()->dispatch($onWidgetCreate);

            session()->flash(['Widget Created'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('widgets.edit', ['widget' => $onWidgetCreate->getWidgetSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating The Widget Item'], input()->fromPost()->all());
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
        $menu = $this->getWidgetData()->selectWithCondition($this->getWidgetData()->getWidgetTable(), ['*'], "widget_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onWidgetCreate = new OnWidgetCreate($menu, $this->getWidgetData());
        view('Modules::Widget/Views/edit', [
            'Data' => $onWidgetCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->widgetUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('widgets.edit', [$slug]));
        }

        try {
            $widgetToUpdate = $this->getWidgetData()->createWidget();
            $widgetToUpdate['widget_slug'] = helper()->slug(input()->fromPost()->retrieve('widget_slug'));

            db(onGetDB: function ($db) use ($slug, $widgetToUpdate) {
                $db->FastUpdate($this->getWidgetData()->getWidgetTable(), $widgetToUpdate, db()->Where('widget_slug', '=', $slug));
            });

            $slug = $widgetToUpdate['widget_slug'];
            helper()->clearAPCUCache();
            session()->flash(['Widget Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('widgets.edit', ['widget' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The Widget Item']);
            redirect(route('widgets.edit', [$slug]));
        }
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getWidgetData()->dataTableUpdateMultiple([
            'id' => 'widget_id',
            'table' => Tables::getTable(Tables::WIDGETS),
            'rules' => $this->widgetUpdateMultipleRule(),
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
        return $this->getWidgetData()->dataTableDeleteMultiple([
            'id' => 'widget_id',
            'table' => Tables::getTable(Tables::WIDGETS),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @return WidgetData
     */
    public function getWidgetData(): WidgetData
    {
        return $this->widgetData;
    }


}
