<?php

namespace App\Modules\Field\EventHandlers\Fields\Menu;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Menu implements HandlerInterface
{

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
            $uniqueSlug = "$menu->menu_slug:$menu->menu_id";
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
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name ((via [[_v('Menu_$inputName.Name')]])
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

{$event->getTemplateEngineFrag($data)}
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
        $inputName = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $menuSlug = (isset($data->menuSlug) && !empty($inputName)) ? $inputName : $data->menuSlug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $menuFrag = '';
        $menuData = new MenuData();
        $menus = $menuData->getMenus();
        foreach ($menus as $menu) {
            $uniqueSlug = "$menu->menu_slug:$menu->menu_id";
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
        $frag .= <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="menuSlug-$changeID">Choose Menu
     <select name="menuSlug" class="default-selector mg-b-plus-1" id="menuSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        $displayName = (isset($data->displayName)) ? $data->displayName : '';
        $inputName = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $menuSlug = (isset($data->menuSlug) && !empty($inputName)) ? $inputName : $data->menuSlug;
        if (empty($menuSlug)) {
            return $frag;
        }
        $menuSlug = explode(':', $menuSlug);
        $menuID = (isset($menuSlug[1]) && is_numeric($menuSlug[1])) ? (int)$menuSlug[1] : '';
        if (empty($menuID)) {
            return $frag;
        }
        $menuData = new MenuData();
        $tree = $menuData->getMenuItems($menuID);
        foreach ($tree as $t) {
            $frag .= $this->getMenuHTMLFragment($t, 0, $displayName);
        }
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        addToGlobalVariable("Menu_$inputName", ['Name' => $displayName, 'InputName' => $inputName, 'Data' => $frag]);
        $event->handleTemplateEngineView($data);
        return '';
    }

    /**
     * @throws \Exception
     */
    protected function getMenuHTMLFragment($menu, $depth = 0, $displayName = '1'): string
    {
        $svgIcon = '';
        if (!empty($menu->mt_icon)) {
            $svgIcon = helper()->htmlSpecChar($menu->mt_icon);
            $svgIcon = "<svg class='icon:admin $svgIcon'><use xlink:href='#$svgIcon'></use></svg>";
        }
        $name = '';
        if ($displayName == '1') {
            $name = <<<HTML
<div class="text:paragraph-fluid-one text:no-wrap">$menu->mt_name</div>
HTML;

        }
        $url = helper()->htmlSpecChar($menu->mt_url_slug);
        $target = 'target="_blank"';
        if ($menu->mt_target == 0) {
            $target = 'target="_self"';
        }
        $htmlFrag = <<<MENU
<li class="menu-block d:flex" data-menu-depth="$depth">
    <a href="$url" $target class="menu-box flex-gap:small color:black border-width:default border:black" title="$menu->mt_name">
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