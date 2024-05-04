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
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateModeError;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class IfBlock extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        $result = false;
        if ($view->validateMaxArg($tagToken->getArg(), 'ifBlock', 100, 2)) {
            $result = true;
        }

        // This checks for odd arg num, which ifBlock should never have
        if (count($tagToken->getArg()) & 1){
            $this->error = "ifBlock Args Should be In The Form [[ifBlock('block-name-1', 'render', 'bloc-name-2', 'render', '...', '...')]]. In Two Steps";
            $result = false;
        }

        foreach ($tagToken->getArg() as $arg){
            if (!$view->getContent()->isBlock($arg)){
                $view->exception(TonicsTemplateModeError::class, [" `$arg` Is Not a Known Block"]);
            }
        }

        return $result;
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent('ifBlock', $tagToken->getContent(), $tagToken->getArg());
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
        $skip = null; $view = $this->getTonicsView();
        foreach ($args as $k => $arg){
            if ($k === $skip){
                continue;
            }
            if (helper()->stripWhiteSpaces($view->renderABlock($arg))){
                return $view->renderABlock($args[++$k]);
            } else {
                $skip = ++$k;
            }
        }

        return '';
    }
}