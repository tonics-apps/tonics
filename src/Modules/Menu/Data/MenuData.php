<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class MenuData extends AbstractDataLayer
{

    const CORE_MENU  = "Core-Menu-aebf7108-7136-4bc6-8ee1-85ad6005d18d";

    use UniqueSlug;

    public function getMenuTable(): string
    {
        return Tables::getTable(Tables::MENUS);
    }

    public function getMenuItemsTable(): string
    {
        return Tables::getTable(Tables::MENU_ITEMS);
    }

    public function getMenuItemPermissionsTable(): string
    {
        return Tables::getTable(Tables::MENU_ITEM_PERMISSION);
    }


    public function getMenuColumns(): array
    {
        return [ 'menu_id', 'menu_name', 'menu_slug', 'created_at', 'updated_at' ];
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
    public function generateMenuTree(): string
    {
        $menus = null;
        db(onGetDB: function (TonicsQuery $db) use (&$menus){
            $permissionTable = Tables::getTable(Tables::PERMISSIONS);
            $rolePermissionTable = Tables::getTable(Tables::ROLE_PERMISSIONS);
            $roleTable = Tables::getTable(Tables::ROLES);

            $menus = $db->run("
SELECT mi.*
FROM {$this->getMenuItemsTable()} mi
JOIN {$this->getMenuItemPermissionsTable()} mp ON mi.slug_id = mp.fk_menu_item_slug_id
JOIN $permissionTable p ON mp.fk_permission_id = p.permission_id
JOIN $rolePermissionTable rp ON p.permission_id = rp.fk_permission_id
JOIN $roleTable r ON rp.fk_role_id = r.role_id
WHERE mi.fk_menu_id = ? AND r.role_name = ? 
ORDER BY mi.id;
", $this->getCoreMenuID(), session()::getUserRoleName());
        });

        $tree = helper()->generateTree(['parent_id' => 'mt_parent_id', 'id' => 'mt_id'], $menus);
        $htmlFrag = '';
        foreach ($tree as $t){
            $htmlFrag .= $this->getCoreMenuHTMLFragment($t);
        }

        return $htmlFrag;
    }

    private function getCoreMenuHTMLFragment($menu, $depth = 0)
    {
        $htmlFrag = <<<MENU
<li class="menu-block" data-menu-depth="$depth">
    <a href="$menu->mt_url_slug" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
        $menu->mt_icon
        <div class="text:paragraph-fluid-one text:no-wrap">$menu->mt_name</div>
MENU;
        if (isset($menu->_children)){
            $htmlFrag .= <<<MENU
        <button class="dropdown-toggle bg:transparent  border:none" aria-expanded="false" aria-label="Expand child menu">
            <svg class="icon:admin tonics-arrow-down">
                <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
            </svg>
        </button>
    </a>
    <!-- The child menu-->
    <ul class="child-menu z-index:child-menu site-navigation-ul flex-gap d:none list:style:none">
MENU;
            $depth = $depth + 1;
            foreach ($menu->_children as $menu){
                $htmlFrag .= $this->getCoreMenuHTMLFragment($menu, $depth);
            }
            $htmlFrag .= <<<MENU
    </ul>
</li>
MENU;
        } else {
            $htmlFrag .= <<<MENU
    </a>
</li>
MENU;
        }

        return $htmlFrag;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCoreMenuID(): mixed
    {
        $coreMenu = $this->getMenuID(self::CORE_MENU, function (TonicsQuery $db){ $db->WhereEquals('menu_can_edit', 0); });
        if ($coreMenu === null){
             db(onGetDB: function (TonicsQuery $db){
                 $table = $this->getMenuTable();
                 $db->Insert($table, ['menu_name' => self::CORE_MENU, 'menu_slug' => self::CORE_MENU, 'menu_can_edit' => 0]);
             });
        } else {
            return $coreMenu;
        }

        return $this->getMenuID(self::CORE_MENU, function (TonicsQuery $db){ $db->WhereEquals('menu_can_edit', 0); });
    }

    /**
     * @param string $slug
     * @param callable|null $where
     * @return mixed
     * @throws \Exception
     */
    public function getMenuID(string $slug, callable $where = null): mixed
    {
        $menu = null;
        db(onGetDB: function (TonicsQuery $db) use ($where, $slug, &$menu){
            $table = $this->getMenuTable();
            $menu = $db->Select('menu_id')->From($table)
                ->WhereEquals('menu_slug', $slug)
                ->when($where, function (TonicsQuery $db) use ($where) {
                    $where($db);
                })->FetchFirst()->menu_id ?? null;
        });
        return $menu;
    }


    /**
     * @throws \Exception
     */
    public function getMenus(): mixed
    {
        $table = $this->getMenuTable();
        $menu = null;
        db(onGetDB: function ($db) use ($table, &$menu){
            $menu = $db->run("SELECT * FROM $table");
        });
        return $menu;
    }

    /**
     * @throws \Exception
     */
    public function getMenuItems(int|string $menuIDOrSlug, bool $generateTree = true): mixed
    {
        $data = null;
        db(onGetDB: function (TonicsQuery $db) use ($menuIDOrSlug, &$data){
            $menuItemsTable = $this->getMenuItemsTable();
            $menuTable = $this->getMenuTable();
            $menuPermissionsTable = $this->getMenuItemPermissionsTable();
            $data = $db->Select("*, GROUP_CONCAT(DISTINCT fk_permission_id) AS fk_permission_id")->From($menuItemsTable)
                ->Join($menuTable, table()->pickTable($menuTable, ['menu_id']), table()->pickTable($menuItemsTable, ['fk_menu_id']))
                ->LeftJoin($menuPermissionsTable, table()->pickTable($menuPermissionsTable, ['fk_menu_item_slug_id']), table()->pickTable($menuItemsTable, ['slug_id']))
                ->when(is_string($menuIDOrSlug),
                    function (TonicsQuery $db) use ($menuIDOrSlug) {
                        $db->WhereEquals('menu_slug', $menuIDOrSlug);
                    },
                    function (TonicsQuery $db) use ($menuIDOrSlug) {
                        $db->WhereEquals('fk_menu_id', $menuIDOrSlug);
                    })
                ->GroupBy("$menuItemsTable.mt_id")
                ->FetchResult();

        });

        if ($data){
            if ($generateTree){
                return helper()->generateTree(['parent_id' => 'mt_parent_id', 'id' => 'mt_id'], $data);
            }
            return $data;
        }

        return [];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getAllPermissions(): mixed
    {
        $permissions = null;
        db(onGetDB: function (TonicsQuery $db) use (&$permissions){
            $permissions = $db->Select('*')->From(Tables::getTable(Tables::PERMISSIONS))->FetchResult();
        });

        return $permissions;
    }

    /**
     * @param $menuItems
     * @return string
     * @throws \Exception
     */
    public function getMenuItemsListing($menuItems): string
    {
        $permissions = $this->getAllPermissions();

        $htmlFrag = '';
        foreach ($menuItems as $menu){
            $htmlFrag .= $this->getMenuItemsListingFrag($menu, $permissions);
        }

        return $htmlFrag;
    }

    protected function getMenuItemsListingFrag($menu, $permissions): string
    {
        $frag =<<<HTML
<li tabindex="0" 
data-id="$menu->mt_id"
data-parentid="$menu->mt_parent_id"
class="width:100%  draggable menu-arranger-li d:flex flex-d:column align-items:center justify-content:center cursor:move no-text-highlight">
            <fieldset class="width:100% padding:default d:flex justify-content:center pointer-events:none">
                <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                    <span class="menu-arranger-text-head">$menu->mt_name</span>
                    <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer" aria-expanded="false" aria-label="Expand child menu" data-menutoggle_click_outside="true">
                        <svg class="icon:admin tonics-arrow-down color:white">
                            <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                        </svg>
                    </button>
                </legend>
                <div class="d:none flex-d:column menu-widget-information pointer-events:all owl width:100%">

                    <div class="form-group d:flex flex-gap:small">
                        <label class="menu-settings-handle-name width:100%">$menu->mt_name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="menu-name" value="$menu->mt_name" placeholder="Overwrite the menu name">
                        </label>
                        
                        <label class="menu-settings-handle-name width:100%">SVG Icon Name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="svg-icon" value="$menu->mt_icon" placeholder="e.g toggle-right">
                        </label>
                    </div>
                   

                    <div class="form-group d:flex flex-gap:small">
                        <label class="menu-settings-handle-name width:100%">Overwrite URL Slug
                            <input id="menu-url-slug" type="text" class="menu-url-slug color:black border-width:default border:black placeholder-color:gray" name="url-slug" value="$menu->mt_url_slug" placeholder="Only Overwrite For a Custom Link">
                        </label>
                        
                        <label class="menu-settings-handle-name width:100%">Optional CSS Classes
                            <input id="edit-menu-item" type="text" class="edit-menu-item-classes color:black border-width:default border:black placeholder-color:gray" name="menu-item-classes" value="$menu->mt_classes" placeholder="Separate By Spaces, e.g class-1 class-2">
                        </label>
                    </div>       
                    <div class="form-group">
                        <label>Link Target
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
    </label>
</div>
HTML;
        if (!empty($permissions)){
            $frag .= <<<HTML
                    <div class="form-group">
                        <label>Select Permission(s)
                        <select multiple name="menuPermissions" class="default-selector" id="menuPermissions">
HTML;

            foreach ($permissions as $permission){
                $select = '';
                $menuPermission = explode(',', $menu->fk_permission_id);
                $menuPermission = array_flip($menuPermission);
                if (isset($menuPermission[$permission->permission_id])){
                    $select = 'selected';
                }

                $frag .="<option value='$permission->permission_id' $select >$permission->permission_display_name</option>";
            }

            $frag .=<<<HTML
    </select>
    </label>
</div>
HTML;
        }


        $frag .=<<<HTML
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
                $frag .= $this->getMenuItemsListingFrag($child, $permissions);
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
    public function createMenu(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getMenuTable(),
            'menu_slug', helper()->slug(input()->fromPost()->retrieve('menu_slug')));

        $menu = []; $postColumns = array_flip($this->getMenuColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $menu[$inputKey] = helper()->date(datetime: $inputValue);
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