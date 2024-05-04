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

namespace App\Modules\Core\Library\View\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class MenuModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'menu');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent('menu', '', $tagToken->getArg());
    }

    public function error(): string
    {
        return $this->error;
    }

    /**
     * @param string $content
     * @param array $args
     * @param array $nodes
     * @return string
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $menuVarKey = array_shift($args);
        $menuArray = $this->getTonicsView()->accessArrayWithSeparator($menuVarKey);

        if (!is_array($menuArray) || count($menuArray) <= 0){
            return '';
        }

        return '<li>' . $this->collateMenu($menuArray) . '</li>';
    }

    public function collateMenu(array $menus): string
    {
        $result = '';
        foreach ($menus as $menu){
            $target = ($menu->mt_target == 1) ? "target=\"_blank\"" : '';
            if (isset($menu->_children)){
                $result .= <<<Menu
<a href="/$menu->mt_url_slug" class="main-link simple-nav-li" $target
            <span>$menu->mt_name</span>
            <button class="main-toggle" aria-expanded="false" aria-label="Expand child menu">
                <svg id="arrow-up-down" class="icon tonics-arrow-down tonics-arrow-up menu-builder">
                    <use xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
        </a>
Menu;
                $result .= "<ul class=\"sub-menu display-none\">";
                $this->collateMenu($menu->_children);
                $result .= '</ul>';
            } else {
                $result .= <<<Menu
        <a href="/$menu->mt_url_slug" $target
           class="simple-nav-li $menu->mt_classes"> $menu->mt_name
        </a>
Menu;
            }
        }

        return $result;
    }

}