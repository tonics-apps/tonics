<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Menu\Controllers;

use App\Configs\AppConfig;
use App\Library\Authentication\Session;
use App\Library\CustomClasses\UniqueSlug;
use App\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Menu\Data\MenuData;
use App\Modules\Menu\Events\OnMenuCreate;
use App\Modules\Menu\Rules\MenuValidationRules;
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
        $cols = '`menu_id`, `menu_name`, `menu_slug`, `created_at`';
        $data = $this->getMenuData()->generatePaginationData(
            $cols,
            'menu_name',
            $this->getMenuData()->getMenuTable());

        $menuListing = '';
        if ($data !== null){
            $menuListing = $this->getMenuData()->adminMenuListing($data->data);
            unset($data->data);
        }

        view('Modules::Menu/Views/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'MenuListing' => $menuListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Menu/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->menuStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all(), Session::SessionCategories_FlashMessageError);
            redirect(route('menus.create'));
        }
        $menu = $this->getMenuData()->createMenu();
        $menuReturning = db()->insertReturning($this->getMenuData()->getMenuTable(), $menu, $this->getMenuData()->getMenuColumns());

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
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $onMenuCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
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
            session()->flash($validator->getErrors(), type: Session::SessionCategories_FlashMessageError);
            redirect(route('menus.edit', [$slug]));
        }

        $menuToUpdate = $this->getMenuData()->createMenu();
        $menuToUpdate['menu_slug'] = helper()->slug(input()->fromPost()->retrieve('menu_slug'));
        $this->getMenuData()->updateWithCondition($menuToUpdate, ['menu_slug' => $slug], $this->getMenuData()->getMenuTable());

        $slug = $menuToUpdate['menu_slug'];
        session()->flash(['Menu Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('menus.edit', ['menu' => $slug]));
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getMenuData()->deleteWithCondition(whereCondition: "menu_slug = ?", parameter: [$slug], table: $this->getMenuData()->getMenuTable());
            session()->flash(['Menu Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('menus.index'));
        } catch (\Exception){
            session()->flash(['Failed To Delete Menu'], type: Session::SessionCategories_FlashMessageError);
            redirect(route('menus.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('menus.index'));
        }

        $this->getMenuData()->deleteMultiple(
            $this->getMenuData()->getMenuTable(),
            array_flip($this->getMenuData()->getMenuColumns()),
            'menu_id',
            onSuccess: function (){
                session()->flash(['Menu Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('menus.index'));
            },
            onError: function (){
                session()->flash(['Failed To Delete Menu'], type: Session::SessionCategories_FlashMessageError);
                redirect(route('menus.index'));
            },
        );
    }

    /**
     * @return MenuData
     */
    public function getMenuData(): MenuData
    {
        return $this->menuData;
    }

}
