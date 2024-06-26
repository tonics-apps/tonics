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

namespace App\Modules\Field\EventHandlers\Fields\Menu;

use App\Modules\Core\Boot\InitLoaderMinimal;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Menu implements HandlerInterface
{
    private array $svgIcon = [];

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'Menus',
            'Add Site Menu',
            'Menu',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
            return $this->userForm($event, $data);
        },
            handleViewProcessing: function ($data) use ($event) {
               return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Menu';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $menuSlug = (isset($data->menuSlug)) ? $data->menuSlug : '';
        $displayName = (isset($data->displayName)) ? $data->displayName : '1';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        if ($displayName === '1') {
            $displayName = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $displayName = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $menuData = new MenuData();
        $menuFrag = '';
        $menus = $menuData->getMenus();
        foreach ($menus as $menu) {
            $uniqueSlug = "$menu->menu_slug";
            if ($menuSlug === $uniqueSlug) {
                $menuFrag .= <<<HTML
<option value="$uniqueSlug" selected>$menu->menu_name</option>
HTML;
            } else {
                $menuFrag .= <<<HTML
<option value="$uniqueSlug">$menu->menu_name</option>
HTML;
            }
        }
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name ((via [[v('Menu_$inputName.Name')]])
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="menuSlug-$changeID">Choose Menu (via [[_v('Menu_$inputName.Data')]])
     <select name="menuSlug" class="default-selector mg-b-plus-1" id="menuSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="displayName-$changeID">Display Menu Names ?
     <select name="displayName" class="default-selector mg-b-plus-1" id="displayName-$changeID">
        $displayName
     </select>
    </label>
</div>

FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Menu';
        $menuSlug = $event->getKeyValueInData($data, $data->inputName);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $menuFrag = '';
        $menuData = new MenuData();
        $menus = $menuData->getMenus();
        foreach ($menus as $menu) {
            $uniqueSlug = "$menu->menu_slug";
            if ($menuSlug === $uniqueSlug) {
                $menuFrag .= <<<HTML
<option value="$uniqueSlug" selected>$menu->menu_name</option>
HTML;
            } else {
                $menuFrag .= <<<HTML
<option value="$uniqueSlug">$menu->menu_name</option>
HTML;
            }
        }
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$menuSlug}_$changeID";
        $frag .= <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="menuSlug-$changeID">Choose Menu
     <select name="$inputName" class="default-selector mg-b-plus-1" id="menuSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data)
    {
        $frag = '';
        $displayName = (isset($data->displayName)) ? $data->displayName : '';
        $fieldData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = !empty(getPostData()) ? getPostData() : $fieldData;
        $menuSlug = (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        if (empty($menuSlug)) {
            return $frag;
        }

        $menuData = new MenuData();
        $tree = $menuData->getMenuItems($menuSlug);
        foreach ($tree as $t) {
            $frag .= $this->getMenuHTMLFragment($t, 0, $displayName);
        }
        InitLoaderMinimal::addToGlobalVariable('Menu.SVG_ICONS', $this->svgIcon);
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        addToGlobalVariable("Menu_$inputName", ['Name' => $displayName, 'InputName' => $inputName, 'Data' => $frag, 'Tree' => $tree]);
        return '';
    }

    /**
     * @throws \Exception
     */
    protected function getMenuHTMLFragment($menu, $depth = 0, $displayName = '1'): string
    {
        $svgIcon = '';
        if (!empty($menu->mt_icon)) {
            if (array_key_exists($menu->mt_icon, helper()->iconSymbols())) {
                $this->svgIcon[$menu->mt_icon] = $menu->mt_icon;
                $svgIcon = "<svg class='icon:admin $svgIcon'><use xlink:href='#tonics-$menu->mt_icon'></use></svg>";
            }
        }
        $menuName = helper()->htmlSpecChar($menu->mt_name);
        $name = '';
        if ($displayName == '1') {
            $name = <<<HTML
<div class="text:paragraph-fluid-one text:no-wrap">$menuName</div>
HTML;

        }
        $url = helper()->htmlSpecChar($menu->mt_url_slug);
        $target = 'target="_blank"';
        if ($menu->mt_target == 0) {
            $target = 'target="_self"';
        }

        $htmlFrag = <<<MENU
<li class="menu-block d:flex" data-menu-depth="$depth">
    <a href="$url" $target class="menu-box flex-gap:small color:black border-width:default border:black" title="$menuName">
        $svgIcon
       $name
MENU;
        if (isset($menu->_children)) {
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
            foreach ($menu->_children as $menu) {
                $htmlFrag .= $this->getMenuHTMLFragment($menu, $depth, $displayName);
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

}