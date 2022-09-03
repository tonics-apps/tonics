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

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;


/**
 * This extension supports the ability to add hook (more like a placeholder reserve for some future content),
 * and the ability to hook into created hooks.
 *
 * <br>
 * To create a hook or a reserved spot, you do:
 *
 * `[[add_hook('hook_name')]]`
 *
 * <br>
 * To use the hook later, you do:
 *
 * ```
 * [[hook_into('in_head')This data would be hooked into in_head]]
 * ```
 *
 * <br>
 * Reset hook using:  `[[reset_hook('in_head')`]]
 *
 * There is no limit to the number of times you can hook_into, so, enjoy ;)
 *
 * <br>
 * Note: add_hook and hook_into can't and shouldn't be nested.
 */
class Hook extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName());
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $tagname = $tagToken->getTagName();
        if ($tagname === 'hook_into' || $tagname === 'place_into'){
            $this->handleHookInto($tagToken);
        }

        if ($tagname === 'add_hook' || $tagname === 'add_placeholder'){
            $view->getContent()->addToContent($tagToken->getTagName(), '', $tagToken->getArg());
            $storage = $view->getModeStorage($tagToken->getTagName());
            if (!isset($storage[$tagToken->getFirstArgChild()])){
                $storage[$tagToken->getFirstArgChild()] = $this->setUpHook();
                $view->storeDataInModeStorage('add_hook', $storage);
                // if add_hook has a children, then we use it as a default for hook_into,
                // this way, we can make things faster a bit when hooking default data
                if (!empty($tagToken->getContent()) || $tagToken->hasChildren()){
                    $new_tag = clone $tagToken->getTag();
                    $hook_into_default = new OnTagToken($new_tag);
                    $hook_into_default->getTag()->setTagName('hook_into');
                    $this->handleHookInto($hook_into_default);
                }
            }
        }

        if ($tagname === 'reset_all_hooks' || $tagname === 'reset_all_placeholder'){
            $storage = $view->getModeStorage('add_hook');
            foreach ($storage as $k => $s){
                $storage[$k]['nodes'] = [];
            }
            $view->storeDataInModeStorage('add_hook', $storage);
        }

        if ($tagname === 'reset_hook' || $tagname === 'reset_placeholder'){
            $storage = $view->getModeStorage('add_hook');
            $hook_name = $tagToken->getFirstArgChild();
            if (isset($storage[$hook_name])){
                $storage[$hook_name]['nodes'] = [];
                $view->storeDataInModeStorage('add_hook', $storage);
            }
        }

    }

    public function error(): string
    {
        return $this->error;
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $current = $this->getTonicsView()->getCurrentRenderingContentMode();
        // this is for hook_into, probably called from a nested tag
        if ($current === 'hook_into' || $current === 'place_into'){
            $tag = (new Tag())->setNodes($nodes)->setArgs($args);
            $tag->setContent($content);
            $onTagToken = new OnTagToken($tag);
            $this->handleHookInto($onTagToken);
        }

        if ($current === 'add_hook' || $current === 'add_placeholder'){
            $hook_name = $args[0];
            $storage = $this->getTonicsView()->getModeStorage('add_hook');
            $output = '';
            if (isset($storage[$hook_name]['nodes'])){
                /**@var Tag $node */
                foreach ($storage[$hook_name]['nodes'] as $node){
                    $mode = $this->getTonicsView()->getModeRendererHandler($node->getTagName());
                    if ($mode instanceof TonicsModeRendererInterface) {
                        $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                        $output .= $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
                    }
                }
                return $output;
            }
        }

        return '';
    }

    public function handleHookInto(OnTagToken $tagToken)
    {
        $hook_name = $tagToken->getFirstArgChild();
        $this->handleContentInTag($tagToken);
        $storage = $this->getTonicsView()->getModeStorage('add_hook');
        // resolve nested add_hook or add_placeholder
        /** @var Tag $node */
        foreach ($tagToken->getTag()->childNodes() as $node){
            if ($node->getTagName() === 'add_hook' || $node->getTagName() === 'add_placeholder'){
                if (!isset($storage[$node->getFirstArgChild()])){
                    $storage[$node->getFirstArgChild()] = ['parent' => $hook_name, 'nodes' => []];
                }
            }
        }

        if (isset($storage[$hook_name])){
            $storage[$hook_name]['nodes'] = [...$storage[$hook_name]['nodes'], ...$tagToken->getTag()->getNodes()];
        }

        $this->getTonicsView()->storeDataInModeStorage('add_hook', $storage);
    }

    public function handleContentInTag(OnTagToken $tagToken)
    {
        if (!empty($tagToken->getContent())){
            $tag = new Tag('char');
            $tag->setContent($tagToken->getContent());
            $tagToken->getTag()->prependTagToNode($tag);
            $tagToken->getTag()->setContent('');
        }
    }

    public function setUpHook(): array
    {
        return [
            'content_position' =>  array_key_last($this->getTonicsView()->getContent()->getContents()),
            'nodes' => [],
        ];
    }

}