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

use App\Modules\Core\Library\View\CustomTokenizerState\Conditional\ConditionalTokenizerState;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRuntimeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * In future version, we can add support for forcing precedence using square bracket,
 *
 * e.g: `e[500/[10 + 2] * 3 * 3 * 3]`
 *
 * or
 *
 * ```
 * [bool[true] && bool[false]] || bool[true]
 * ```
 *
 * For now, it doesn't support that.
 */
class IfCondition extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{


    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'if');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view =  $this->getTonicsView();
        $result = $this->handleConditionalTokenization($tagToken->getTag());
        $view->getContent()->addToContent('if', $tagToken->getContent(), $result);
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
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        /** @var $conditionalView TonicsView  */
        /** @var $node Tag  */
        /** @var $tag Tag  */
        if (!isset($args['conditionalView'])){
            $result = $this->handleConditionalTokenization((new Tag())->setArgs($args)->setNodes($nodes));
            $conditionalView = $result['conditionalView'];
            $tag = $result['tag'];
        } else {
            $conditionalView = $args['conditionalView'];
            $tag = $args['tag'];
        }

        $conditionalView->setCharacters($tag->getArgs());
        $conditionalView->addToCharacters('EOF');
        $conditionalView->switchState(ConditionalTokenizerState::InitEvaluateState);

        $conditionalView->tokenize();
        # Reset so the next if ConditionalView can start from scratch
        $conditionalView->reset();

        $ifOutPut = '';
        if ($conditionalView->getModeStorage('if')['result']){
            foreach ($tag->getNodes() as $node) {
                $mode = $this->getTonicsView()->getModeRendererHandler($node->getTagName());
                if ($mode instanceof TonicsModeRendererInterface) {
                    $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                    $ifOutPut .= $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
                }
            }

            $ifOutPut = $content . $ifOutPut;
        }

        return $ifOutPut;
    }

    public function setConditionalTokenizerState(): void
    {
        $view =  $this->getTonicsView();
        $storage = $view->getModeStorage('if');
        if (!isset($storage['conditionalView'])){
            $newView = new TonicsView();
            $newView->setContent(new Content());
            $newView->setTemplateLoader($view->getTemplateLoader());
            $newView->setTokenizerState(new ConditionalTokenizerState());
            $newView->setModeHandler($view->getModeHandler());
            $newView->setModeStorages($view->getModeStorages());
            $storage['conditionalView'] = $newView;
        }

        $storage['conditionalView']->clearStackOfOpenEl();
        $storage['conditionalView']->setLine($view->getLine());
        $view->storeDataInModeStorage('if', $storage);
    }

    /**
     * You get an array with item:
     *
     * - tag: which is a Tag
     * - conditionalView: which is a TonicsView Instance of the conditionalStateImplementation
     * @param Tag $tagToken
     * @return array
     */
    public function handleConditionalTokenization(Tag $tagToken): array
    {
        $this->setConditionalTokenizerState();
        $view = $this->getTonicsView();
        /** @var $conditionalView TonicsView  */

        $conditionalView = $view->getModeStorage('if')['conditionalView'];
        # For some weird reason we need to instantiate a new content everytime, otherwise we get a partial final output
        $conditionalView->setContent(new Content());

        $conditionalView->reset()->splitStringCharByChar($tagToken->getArgs()[0]);
        $conditionalView->tokenize();
        $tagToken->setArgs($conditionalView->getLastOpenTag()->getArgs())->setContextFree(false);
        foreach ($tagToken->getChildrenRecursive($tagToken) as $tag){
            /** @var Tag $tag */
            if ($tag->getTagName() === 'if'){
                $view->exception(TonicsTemplateRuntimeException::class, ["Nested If is not Supported"]);
                // For Nested If or ElseIf, but it isn't supported for now and might never be
                /*$newView->reset()->splitStringCharByChar($tag->getArgs()[0]);
                $newView->tokenize();
                $tag->setArgs($newView->getLastOpenTag()->getArgs());*/
            }
        }

        $conditionalView->reset()
            ->setContent($view->getContent())->setVariableData($view->getVariableData());

        $storage = $view->getModeStorage('if');
        $storage['conditionalView'] = $conditionalView;
        $view->storeDataInModeStorage('if', $storage);
        return [
            'tag' => $tagToken,
            'conditionalView' => $conditionalView
        ];
    }
}