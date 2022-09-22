<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Menu\Data\MenuData;
use App\Modules\Menu\Events\OnMenuCreate;
use App\Modules\Menu\Rules\MenuValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class MenuController
{
    use UniqueSlug, MenuValidationRules, Validator;

    private MenuData $menuData;

    public function __construct(MenuData $menuData)
    {
        $this->menuData = $menuData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $table = Tables::getTable(Tables::MENUS);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::MENUS . '::' . 'menu_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'menu_id'],
            ['type' => 'text', 'slug' => Tables::MENUS . '::' . 'menu_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'menu_name'],
            ['type' => 'date_time_local', 'slug' => Tables::MENUS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = '*, CONCAT("/admin/tools/menu/", menu_slug, "/edit" ) as _edit_link, CONCAT("/admin/tools/menu/items/", menu_slug, "/builder") as _builder_link';

        $data = db()->Select($tblCol)
            ->From($table)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('menu_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));


        view('Modules::Menu/Views/index', [
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
        if ($this->getMenuData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getMenuData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        view('Modules::Menu/Views/create');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->menuStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('menus.create'));
        }
        $menu = $this->getMenuData()->createMenu();
        $menuReturning = db()->insertReturning($this->getMenuData()->getMenuTable(), $menu, $this->getMenuData()->getMenuColumns(), 'menu_id');

        $onMenuCreate = new OnMenuCreate($menuReturning, $this->getMenuData());
        event()->dispatch($onMenuCreate);

        session()->flash(['Menu Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('menus.edit', ['menu' => $onMenuCreate->getMenuSlug()]));
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $menu = $this->getMenuData()->selectWithCondition($this->getMenuData()->getMenuTable(), ['*'], "menu_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onMenuCreate = new OnMenuCreate($menu, $this->getMenuData());
        view('Modules::Menu/Views/edit', [
            'Data' => $onMenuCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->menuUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('menus.edit', [$slug]));
        }

        $menuToUpdate = $this->getMenuData()->createMenu();
        $menuToUpdate['menu_slug'] = helper()->slug(input()->fromPost()->retrieve('menu_slug'));
        db()->FastUpdate($this->getMenuData()->getMenuTable(), $menuToUpdate, db()->Where('menu_slug', '=', $slug));

        $slug = $menuToUpdate['menu_slug'];
        session()->flash(['Menu Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('menus.edit', ['menu' => $slug]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getMenuData()->dataTableUpdateMultiple('menu_id', Tables::getTable(Tables::MENUS), $entityBag, $this->menuUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getMenuData()->dataTableDeleteMultiple('menu_id', Tables::getTable(Tables::MENUS), $entityBag);
    }

    /**
     * @return MenuData
     */
    public function getMenuData(): MenuData
    {
        return $this->menuData;
    }

}
