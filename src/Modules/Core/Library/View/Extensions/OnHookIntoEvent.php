<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 1, 0);
    }

    /**
     * @throws \Exception
     */
    public function stickToContent(OnTagToken $tagToken)
    {
        $this->getTonicsView()->getContent()->addToContent($tagToken->getTagName(), '', $tagToken->getArg());
    }

    public function error(): string
    {
        return '';
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
        $this->handleHookIntoTemplateEventDispatcher($args);
        return '';
    }

    /**
     * @throws \Exception
     */
    public function handleHookIntoTemplateEventDispatcher($args)
    {
        $onHookIntoTemplateEvent = new OnHookIntoTemplate($this->getTonicsView());
        event()->dispatch($onHookIntoTemplateEvent);

        $hookers = $onHookIntoTemplateEvent->getHookInto();
        $storage = $this->getTonicsView()->getModeStorage('add_hook');

        if (!empty($args)){
            $hook_name = $args[0];
            foreach ($hookers as $hooker){
                $hook_into = $hooker['hook_into'];
                if ($hook_name === $hook_into){
                    $handler = $hooker['handler'];
                    if (isset($storage[$hook_into])){
                        $tag = new Tag('char');
                        $tag->setContent($handler($this->getTonicsView()) ?? '');
                        $storage[$hook_into]['nodes'] = [...$storage[$hook_into]['nodes'], $tag];
                    }
                    break;
                }
            }
        } else {
            foreach ($hookers as $hooker){
                $hook_into = $hooker['hook_into'];
                $handler = $hooker['handler'];

                if (isset($storage[$hook_into])){
                    $tag = new Tag('char');
                    $tag->setContent($handler($this->getTonicsView()) ?? '');
                    $storage[$hook_into]['nodes'] = [...$storage[$hook_into]['nodes'], $tag];
                }
            }
        }

        $this->getTonicsView()->storeDataInModeStorage('add_hook', $storage);
    }
}