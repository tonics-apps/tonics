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
 * Unlike the use mode handler that waits until the final rendering period to use a block, this Mode Handler
 * trigger a block on the fly.
 */
class TriggerBlockOnTheFly extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'trigger_block');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $block_name = $tagToken->getFirstArgChild();
        $this->getTonicsView()->renderABlock($block_name);
    }

    public function error(): string
    {
        return '';
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $block_name = $args[0];
        $this->getTonicsView()->renderABlock($block_name);
        return '';
    }
}