<?php

namespace App\Modules\Menu\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Menu\Data\MenuData;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Menu\Rules\MenuValidationRules;
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
     */
    public function index(string $slug)
    {

        $onMenuMetaBox = new OnMenuMetaBox();
        $dispatched = event()->dispatch($onMenuMetaBox);

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
            'SiteURL' => AppConfig::getAppUrl(),
            'MetaBox' => $dispatched->generateMenuMetaBox(),
            'MenuItems' => $this->getMenuData()->getMenuItemsListing($this->getMenuData()->getMenuItems($menuID)),
            'MenuLocation' => $this->getMenuData()->getMenuLocationListing($this->getMenuData()->getMenuLocationRows(), $menuID),
            'MenuBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'MenuSlug' => $slug,
            'MenuID' => $menuID,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $menuSlug = input()->fromPost()->retrieve('menuSlug', '');

        # Stage One: Extract The menuDetails...
        try {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuDetails'), true);
            $validator = $this->getValidator()->make($menuDetails, $this->menuItemsStoreRule());
        }catch (\Exception){
            session()->flash(['An Error Occurred Extracting Menu Data'], []);
            redirect(route('menus.items.index', ['menu' => $menuSlug]));
        }

        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()) {
            try {
                db()->beginTransaction();
                # Delete All the Menu Items Related to $menuDetails->menuID
                $this->getMenuData()->deleteWithCondition(
                    whereCondition: "fk_menu_id = ?", parameter: [$menuDetails['menuID']], table: $this->getMenuData()->getMenuItemsTable());
                # Reinsert it
                db()->insertBatch($this->getMenuData()->getMenuItemsTable(), $menuDetails['menuItems']);
                # Insert or Update The Location
                db()->insertOnDuplicate($this->getMenuData()->getMenuLocationTable(), $menuDetails['menuLocation'], ['fk_menu_id', 'ml_name']);
                db()->commit();
                $error = true;
            } catch (Exception $exception) {

            }
        }


        if ($error === false) {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuDetails'), true);
            session()->flash($validator->getErrors(), $menuDetails ?? []);
            redirect(route('menus.items.index', ['menu' => $menuSlug]));
        }
        session()->flash(['Menu Successfully Saved'], $menuDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
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