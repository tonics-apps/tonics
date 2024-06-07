<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

/**
 * `SetMode` give you access to set a variable name, here is an example:
 *
 * Suppose, you have a data stored in the following location: App_Config.SiteURL and you want to reset it in the location: Data.new_var_name,
 * you do the following:
 *
 * <br>
 * `[[set('Data.new_var_name', 'App_Config.SiteURL')]]`
 *
 * <br>
 * Note: that this won't auto-escape it or do anything of such, since you can do that whenever you use the v mode, so, the variable location could be accessed this way:
 *
 * `[[v('Data.new_var_name')]]`
 *
 * <br>
 * Note: If you are inheriting a template, and you are using the SetMode, the set would only ever work in the context of another ModeHandler,
 * e.g. Block, Hooks, it can't be independent if it is being inherited from another template, hope you get that.
 */
class SetModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate (OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'Set', 2, 2);
    }

    public function stickToContent (OnTagToken $tagToken): void
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent('set', '', $tagToken->getArg());
    }

    public function error (): string
    {
        return $this->error;
    }

    public function render (string $content, array $args, array $nodes = []): string
    {
        $view = $this->getTonicsView();
        if ($view->checkArrayKeyExistence($args[1])) {
            $view->addToVariableData($args[0], $view->accessArrayWithSeparator($args[1]));
        } else {
            $view->addToVariableData($args[0], $args[1]);
        }
        return '';
    }
}