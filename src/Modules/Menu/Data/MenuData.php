<?php

namespace App\Modules\Menu\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;

class MenuData extends AbstractDataLayer
{
    use UniqueSlug;

    public function getMenuTable(): string
    {
        return Tables::getTable(Tables::MENUS);
    }

    public function getMenuItemsTable(): string
    {
        return Tables::getTable(Tables::MENU_ITEMS);
    }

    public function getMenuLocationTable(): string
    {
        return Tables::getTable(Tables::MENU_LOCATIONS);
    }

    public function getMenuColumns(): array
    {
        return [ 'menu_id', 'menu_name', 'menu_slug', 'created_at', 'updated_at' ];
    }

    public function getMenuLocationColumns(): array
    {
        return [ 'ml_id', 'ml_name', 'ml_slug', 'fk_menu_id' ];
    }

    public function getMenuItemsColumns(): array
    {
        return [
            'id', 'fk_menu_id', 'mt_id', 'mt_parent_id', 'mt_name', 'mt_icon', 'mt_classes', 'mt_target', 'mt_url_slug', 'created_at', 'updated_at'
        ];
    }

    /**
     * @throws \Exception
     */
    public function getMenuLocationRows()
    {
        $locationTable = $this->getMenuLocationTable();
        return db()->run("SELECT * FROM $locationTable;");
    }

    /**
     * @param string $slug
     * @return mixed
     * @throws \Exception
     */
    public function getMenuID(string $slug): mixed
    {
        $table = $this->getMenuTable();
        return db()->row("SELECT `menu_id` FROM $table WHERE `menu_slug` = ?", $slug)->menu_id ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getMenus(): mixed
    {
        $table = $this->getMenuTable();
        return db()->run("SELECT * FROM $table");
    }

    /**
     * @throws \Exception
     */
    public function getMenuItems(int $fkMenuID, bool $generateTree = true): mixed
    {
        $table = $this->getMenuItemsTable();
        $data = db()->run("SELECT * FROM $table WHERE `fk_menu_id` = ?", $fkMenuID);
        if ($data){
            if ($generateTree){
                return helper()->generateTree(['parent_id' => 'mt_parent_id', 'id' => 'mt_id'], $data);
            }
            return $data;
        }

        return [];
    }

    /**
     * @param $menuItems
     * @return string
     */
    public function getMenuItemsListing($menuItems): string
    {
        $htmlFrag = '';
        foreach ($menuItems as $menu){
            $htmlFrag .= $this->getMenuItemsListingFrag($menu);
        }

        return $htmlFrag;
    }

    protected function getMenuItemsListingFrag($menu): string
    {
        $frag =<<<HTML
<li tabindex="0" 
data-id="$menu->mt_id"
data-parentid="$menu->mt_parent_id"
class="width:100%  draggable menu-arranger-li d:flex flex-d:column align-items:center justify-content:center cursor:move no-text-highlight">
            <span class="width:100% height:100% z-index:hidden-over-draggable draggable-hidden-over"></span>
            <fieldset class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center pointer-events:none">
                <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                    <span class="menu-arranger-text-head">$menu->mt_name</span>
                    <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer" aria-expanded="false" aria-label="Expand child menu" data-menutoggle_click_outside="true">
                        <svg class="icon:admin tonics-arrow-down color:white">
                            <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                        </svg>
                    </button>
                </legend>
                <div class="d:none flex-d:column menu-widget-information pointer-events:all owl width:100%">

                    <div class="form-group">
                        <label class="menu-settings-handle-name" for="menu-name">$menu->mt_name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="menu-name" value="$menu->mt_name" placeholder="Overwrite the menu name">
                        </label>
                    </div>
                    
                     <div class="form-group">
                        <label class="menu-settings-handle-name" for="menu-name">SVG Icon Name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="svg-icon" value="$menu->mt_icon" placeholder="e.g tonics-toggle-right">
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="menu-settings-handle-name" for="menu-url-slug">Overwrite URL Slug
                            <input id="menu-url-slug" type="text" class="menu-url-slug color:black border-width:default border:black placeholder-color:gray" name="url-slug" value="$menu->mt_url_slug" placeholder="Only Overwrite For a Custom Link">
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="menu-settings-handle-name" for="edit-menu-item">Optional CSS Classes
                            <input id="edit-menu-item" type="text" class="edit-menu-item-classes color:black border-width:default border:black placeholder-color:gray" name="menu-item-classes" value="$menu->mt_classes" placeholder="Separate By Spaces, e.g class-1 class-2">
                        </label>
                    </div>            
                    <div class="form-group">
                        <select name="linkTarget" class="default-selector" id="link-target">
                            <option value="0" disabled="">Link Target</option>
HTML;
        $targets = [0 => 'Same Tab', 1 => 'New Tab'];
        foreach ($targets as $k => $target){
            if ((int)$menu->mt_target === $k){
                $frag .="<option value='$k' selected>$target</option>";
            } else {
                $frag .="<option value='$k'>$target</option>";
            }
        }

        $frag .=<<<HTML
                        </select>
                    </div>

                    <div class="form-group">
                        <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                            Delete Menu Item
                        </button>
                    </div>
                </div>
            </fieldset>
            <ul class="menu-arranger-li-sub width:90%">
HTML;
        if (isset($menu->_children)){
            foreach ($menu->_children as $child){
                $frag .= $this->getMenuItemsListingFrag($child);
            }
        }
        $frag .=<<<HTML
            </ul>
        </li>
HTML;

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function adminMenuListing($menus): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        foreach ($menus as $k => $menu) {
            $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$menu->menu_id"  
    data-menu_id="$menu->menu_id" 
    data-menu_slug="$menu->menu_slug" 
    data-menu_name="$menu->menu_name"
    data-db_click_link="/admin/tools/menu/$menu->menu_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:move no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$menu->menu_name</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$menu->menu_name</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="/admin/tools/menu/$menu->menu_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                        
                         <a href="/admin/tools/menu/items/$menu->menu_slug/builder" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Builder</a>
                   
                   <form method="post" class="d:contents" action="/admin/tools/menu/$menu->menu_slug/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete</button>
                    </form>
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $htmlFrag;
    }

    /**
     * @param $menuLocations
     * @param $menuID
     * @return string
     */
    public function getMenuLocationListing($menuLocations,  $menuID): string
    {
        $frag = '';
        foreach ($menuLocations as $location){
            if ($location->fk_menu_id === $menuID){
             $checkExisting =<<<HTML
<input type="checkbox" 
data-ml-id="$location->fk_menu_id" 
data-ml-name="$location->ml_name"
data-ml-slug="$location->ml_slug" 
id="$location->ml_slug" name="menu-location" value="$location->ml_slug" checked="checked">
<label for="$location->ml_slug">$location->ml_name
</label>
HTML;
            }else{
                $checkExisting =<<<HTML
<input type="checkbox" 
data-ml-id="$location->fk_menu_id" 
data-ml-name="$location->ml_name"
data-ml-slug="$location->ml_slug" 
id="$location->ml_slug" name="menu-location" value="$location->ml_slug">
<label for="$location->ml_slug">$location->ml_name
</label>
HTML;
            }
            $frag .=<<<HTML
 <li class="menu-item">
 $checkExisting
</li>
HTML;

        }

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function createMenu(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getMenuTable(),
            'menu_slug', helper()->slug(input()->fromPost()->retrieve('menu_slug')));

        $menu = []; $postColumns = array_flip($this->getMenuColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $menu[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'menu_slug'){
                    $menu[$inputKey] = $slug;
                    continue;
                }
                $menu[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $menu);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($menu[$v]);
            }
        }

        return $menu;
    }
}