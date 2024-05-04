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

namespace App\Modules\Menu\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Menu\Data\MenuData;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Menu\Rules\MenuValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class MenuControllerItems extends Controller
{
    use MenuValidationRules, Validator;

    private MenuData $menuData;

    public function __construct(MenuData $menuData)
    {
        $this->menuData = $menuData;
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function index(string $slug)
    {

        $onMenuMetaBox = new OnMenuMetaBox();
        $dispatched = event()->dispatch($onMenuMetaBox);

        if (url()->getHeaderByKey('action') === 'default-menu') {
            $name = url()->getHeaderByKey('name');
            $url = url()->getHeaderByKey('url');
            helper()->onSuccess($this->getMenuData()->getDefaultMenuListingFrag($name, $url));
        }

        /** @var OnMenuMetaBox $dispatched */
        if (url()->getHeaderByKey('menuboxname')) {
            $menuBoxName = url()->getHeaderByKey('menuboxname');

            if (url()->getHeaderByKey('action') === 'more' || url()->getHeaderByKey('action') === 'search') {
                $frag = $dispatched->getMoreMenuBoxItems($menuBoxName);
                helper()->onSuccess($frag);
            }
        }

        $menuID = $this->getMenuData()->getMenuID($slug);
        if ($menuID === null) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        view('Modules::Menu/Views/Items/index', [
            'MetaBox' => $dispatched->generateMenuMetaBox(),
            'MenuItems' => $this->getMenuData()->getMenuItemsListing($this->getMenuData()->getMenuItems($menuID)),
            'MenuDefault' => $this->getMenuData()->getDefaultMenuListingFrag('Example Menu'),
            'MenuBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'MenuSlug' => $slug,
            'MenuID' => $menuID,
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(): void
    {
        $menuSlug = input()->fromPost()->retrieve('menuSlug', '');

        # Stage One: Extract The menuDetails...
        try {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuDetails'), true);
            $validator = $this->getValidator()->make($menuDetails ?? [], $this->menuItemsStoreRule());
        } catch (\Exception){
            session()->flash(['An Error Occurred Extracting Menu Data'], []);
            redirect(route('menus.items.index', ['menu' => $menuSlug]));
        }

        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()) {
            try {
                db(onGetDB: function (TonicsQuery $db) use ($menuDetails) {
                    $db->beginTransaction();
                    # Delete All the Menu Items Related to $menuDetails->menuID
                    $db->FastDelete($this->getMenuData()->getMenuItemsTable(), db()->WhereEquals('fk_menu_id', $menuDetails['menuID']));
                    # Reinsert it
                    $db->Insert($this->getMenuData()->getMenuItemsTable(), $menuDetails['menuItems']);
                    # Insert Permissions
                    if (!empty($menuDetails['menuItemPermissions'])){
                        $db->Insert($this->getMenuData()->getMenuItemPermissionsTable(), $menuDetails['menuItemPermissions']);
                    }
                    $db->commit();
                });
                $error = true;
            } catch (Exception $exception) {
                // log..
            }
        }


        if ($error === false) {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuDetails'), true);
            session()->flash($validator->getErrors(), $menuDetails ?? []);
            redirect(route('menus.items.index', ['menu' => $menuSlug]));
        }
        session()->flash(['Menu Successfully Saved'], $menuDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('menus.items.index', ['menu' => $menuSlug]));
    }

    /**
     * @return MenuData
     */
    public function getMenuData(): MenuData
    {
        return $this->menuData;
    }
}