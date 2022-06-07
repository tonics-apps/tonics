<?php

namespace App\Library\View\Extensions;

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
     * @return string
     * @throws \Exception
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