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
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
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
        view('Modules::Menu/Views/index');
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
            session()->flash(['Failed To Delete Menu']);
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
                session()->flash(['Failed To Delete Menu']);
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
