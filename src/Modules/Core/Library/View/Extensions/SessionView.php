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

/**
 * SessionView gives access to the session function in view, you'll be able to call must function you can call with `session()`
 *
 * You can use it as follows:
 * `[[session('function-name', 'arg1', 'arg2')`]]
 *
 * <br>
 * Note: If the session function is not something you can output, use:
 * `[[__session('function-name', 'arg1', 'arg2')`]]`
 * <br>
 * Yh, prefix the session with double under-score
 */
class SessionView extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'session', 4);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $tagName = $tagToken->getTagName();
        if ($tagName === 'session'){
            $view->getContent()->addToContent('session', '', $tagToken->getArg());
        }

        if ($tagName === '__session'){
            $view->getContent()->addToContent('__session', '', $tagToken->getArg());
        }
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
     * @throws \Exception
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $view = $this->getTonicsView();
        $sessionFunc = array_shift($args);
        $currentRenderingMode = $view->getCurrentRenderingContentMode();


        $result = '';
        try {
            if (!empty($args)){
                $result = call_user_func_array(array(session(), $sessionFunc), $args);
            } else {
                $result = call_user_func(array(session(), $sessionFunc));
            }
        } catch (\Exception $exception){
            // Log..
        }

        if ($currentRenderingMode === '__session'){
            return '';
        }

        if ($result === null){
            return '';
        }
        return $result;
    }
}