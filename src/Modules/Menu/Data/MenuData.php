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
    use UniqueSlug;

    public function getMenuTable(): string
    {
        return Tables::getTable(Tables::MENUS);
    }

    public function getMenuItemsTable(): string
    {
        return Tables::getTable(Tables::MENU_ITEMS);
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
     * @param string $slug
     * @return mixed
     * @throws \Exception
     */
    public function getMenuID(string $slug): mixed
    {
        $menu = null;
        db(onGetDB: function ($db) use ($slug, &$menu){
            $table = $this->getMenuTable();
            $menu = $db->row("SELECT `menu_id` FROM $table WHERE `menu_slug` = ?", $slug)->menu_id ?? null;
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
        db(onGetDB: function ($db) use ($menuIDOrSlug, &$data){
            $menuItemsTable = $this->getMenuItemsTable();
            $menuTable = $this->getMenuTable();
            $data = $db->Select('*')->From($menuItemsTable)
                ->Join($menuTable, table()->pickTable($menuTable, ['menu_id']), table()->pickTable($menuItemsTable, ['fk_menu_id']))
                ->when(is_string($menuIDOrSlug),
                    function (TonicsQuery $db) use ($menuIDOrSlug) {
                        $db->WhereEquals('menu_slug', $menuIDOrSlug);
                    },
                    function (TonicsQuery $db) use ($menuIDOrSlug) {
                        $db->WhereEquals('fk_menu_id', $menuIDOrSlug);
                    })
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

                    <div class="form-group">
                        <label class="menu-settings-handle-name" for="menu-name">$menu->mt_name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="menu-name" value="$menu->mt_name" placeholder="Overwrite the menu name">
                        </label>
                    </div>
                    
                     <div class="form-group">
                        <label class="menu-settings-handle-name" for="menu-name">SVG Icon Name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="svg-icon" value="$menu->mt_icon" placeholder="e.g toggle-right">
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