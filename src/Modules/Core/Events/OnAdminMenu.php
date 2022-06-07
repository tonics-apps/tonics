<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Events;

use App\Library\Authentication\Session;
use App\Modules\Core\Data\UserData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

/**
 * Listen to this event if you want to add menu to the admin dashboard
 *
 * Class OnAdminMenu
 * @package Modules\Core\Events
 */
class OnAdminMenu implements EventInterface
{
    const DashboardMenuID = 100;
    const PageMenuID = 200;
    const BlogMenuID = 300;

    const MediaMenuID = 400;
        const TrackMenuID = 500;
        const GenreMenuID = 600;
        const ArtistMenuID = 700;
        const FileManagerMenuID = 800;

    const ToolsMenuID = 900;
        const MenusMenuID = 1000;
        const WidgetsMenuID = 1100;
        const FieldMenuID = 1200;
        const LicenseMenuID = 1300;

    const ExtensionMenuID = 1400;
        const SettingsMenuID = 1500;
        const ThemesMenuID = 1600;
        const PluginMenuID = 1700;

    const ImportsMenuID = 1800;


    private array $MenuSettings = [];
    private mixed $userRole;


    public function __construct()
    {
        $this->userRole = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
    }

    public function userRole()
    {
        return $this->userRole;
    }

    /**
     * @param $condition
     * @param callable $callback
     * @return OnAdminMenu
     */
    public function if($condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this);
        }
        return $this;
    }

    /**
     * @param int $id
     * Menu id or position in the hierarchy
     * @param string $name
     * Name of the Menu
     * @param string $svgIcon
     * The sprite icon
     * @param string $route
     * Menu Link
     * @param int|null $parent
     * Where do you want to place the menu, this is null by default
     * <br>
     * If $parent is null, then it means you want the menu to be a parent, if you want a sub-menu
     * under a parent, you pass the id of the parent on the sub-menu $parent parameter. Just be sure the parent id is accurate, otherwise, it would blow up
     * @return OnAdminMenu
     * @throws \Exception
     */
    public function addMenu(
        int $id,
        string $name,
        string $svgIcon,
        string $route,
        int $parent = null
    ): static
    {

        $this->MenuSettings[] = (object)[
            'id' => $id,
            '_parent' => $parent,
            'name' => $name,
            'slug' => helper()->slug($name),
            'svgIcon' => $svgIcon,
            'route' => $route
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getMenuSettings(): array
    {
        return $this->MenuSettings;
    }

    /**
     * This generates admin menu and arrange them in a recursive manner
     * @throws \Exception
     */
    public function generateMenuTree(): string
    {
        $adminMenus = $this->getMenuSettings();
        // sort the menu by the id
        usort($adminMenus, function ($id1, $id2) {
            return $id1->id <=> $id2->id;
        });
        $tree = helper()->generateTree(['parent_id' => '_parent', 'id' => 'id'], $adminMenus);
        $htmlFrag = '';
        foreach ($tree as $t){
            $htmlFrag .= $this->getMenuHTMLFragment($t);
        }

        return $htmlFrag;
    }

    protected function getMenuHTMLFragment($menu, $depth = 0): string
    {
        $htmlFrag = <<<MENU
<li class="menu-block" data-menu-depth="$depth">
    <a href="$menu->route" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
        $menu->svgIcon
        <div class="text:paragraph-fluid-one text:no-wrap">$menu->name</div>
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
                $htmlFrag .= $this->getMenuHTMLFragment($menu, $depth);
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
     * @return $this
     */
    public function event(): static
    {
        return $this;
    }
}
