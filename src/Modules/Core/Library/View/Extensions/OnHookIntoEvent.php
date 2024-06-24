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

use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class OnHookIntoEvent extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    public function validate (OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 1, 0);
    }

    /**
     * @throws \Exception
     */
    public function stickToContent (OnTagToken $tagToken): void
    {
        $this->getTonicsView()->getContent()->addToContent($tagToken->getTagName(), '', $tagToken->getArg());
    }

    public function error (): string
    {
        return '';
    }

    /**
     * @param string $content
     * @param array $args
     * @param array $nodes
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function render (string $content, array $args, array $nodes = []): string
    {
        $this->handleHookIntoTemplateEventDispatcher($args);
        return '';
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleHookIntoTemplateEventDispatcher ($args): void
    {
        $onHookIntoTemplateEvent = new OnHookIntoTemplate($this->getTonicsView());
        event()->dispatch($onHookIntoTemplateEvent);

        $hookers = $onHookIntoTemplateEvent->getHookInto();
        $storage = $this->getTonicsView()->getModeStorage('add_hook');

        #
        # If $args isn't empty, then it means we are only hooking into a specific hook_name,
        # this is so, we don't get repeated and unnecessary hookers when we hook_into several hooks withing a single class,
        # on a norm, when we use several hooks withing a single class, it shouldn't give us any problem but this would cause a mess and duplication
        # in a certain context, e.g. the `[[each()]` context or any context that is a late renderer
        #
        if (!empty($args)) {
            $hook_name = $args[0];
            foreach ($hookers as $hooker) {
                $hook_into = $hooker['hook_into'];
                if ($hook_name === $hook_into) {
                    $handler = $hooker['handler'];
                    if (isset($storage[$hook_into])) {
                        $tag = new Tag('char');
                        $handlerInit = $handler($this->getTonicsView()) ?? '';
                        $tag->setContent($handlerInit);
                        $storage[$hook_into]['nodes'] = [...$storage[$hook_into]['nodes'], $tag];
                    }
                    // break;
                }
            }
        } else {
            foreach ($hookers as $hooker) {
                $hook_into = $hooker['hook_into'];
                $handler = $hooker['handler'];
                if (isset($storage[$hook_into])) {
                    $tag = new Tag('char');
                    $handlerInit = $handler($this->getTonicsView()) ?? '';
                    $tag->setContent($handlerInit);
                    $storage[$hook_into]['nodes'] = [...$storage[$hook_into]['nodes'], $tag];
                }
            }
        }

        $this->getTonicsView()->storeDataInModeStorage('add_hook', $storage);
    }
}