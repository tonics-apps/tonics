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
 * EachLoop was created to aid in quick development of themes most especially query loops customization,
 * it is not fully done yet as `continue` and `break` statement is still missing,
 * but it is still usable for quick theme building...
 *
 * Usage:
 *
 * ```
 * [[each("number in numbers")
 *      <ul>
 *          <li>[[v("_loop.index")]]
 *              [[if("v[number.license]")
 *                  [[each("license in number.license")
 *                      <ul>
 *                          <li>[[v("license")]]</li>
 *                      </ul>
 *                  ]]
 *              ]]
 *          </li>
 *      </ul>
 * ]]
 * ```
 *
 */
class EachLoop extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    protected array $debug = [];

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'each');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        /** @var $node Tag */
        $view = $this->getTonicsView();
        $explodedArgs = explode(" ", $tagToken->getArg()[0]);
        $this->validateEach($explodedArgs);
        $tagToken->getTag()->setArgs(['_name' => $explodedArgs[0], '_variable' => $explodedArgs[array_key_last($explodedArgs)]]);

        if ($tagToken->hasChildren()) {
            foreach ($tagToken->getChildren(true) as $node) {
                if ($node->getTagName() === 'each' || $node->getTagName() === 'foreach') {
                    $explodedArgs = explode(" ", $node->getArgs()[0]);
                    $this->validateEach($explodedArgs);
                    $node->setArgs(['_name' => $explodedArgs[0], '_variable' => $explodedArgs[array_key_last($explodedArgs)]]);
                }
            }
        }
        $view->getContent()->addToContent('each', $tagToken->getContent(), ['loop' => $tagToken]);
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
        /** @var $node Tag */
        /** @var $tag Tag */
        $view = $this->getTonicsView();
        $tag = (isset($args['loop'])) ? $args['loop']->getTag() : (new Tag())->setArgs($args)->setNodes($nodes);
        if (!isset($tag->getArgs()['_variable'])) {
            if (empty($nodes)) {
                # This could either be `continue` or `break` call, since they can't render, we return empty
                return '';
            }

            # fresh or from an unknown context
            $eachOnTagToken = new OnTagToken($tag);
            $this->stickToContent($eachOnTagToken);
        }

        $loopVariable = $view->accessArrayWithSeparator($tag->getArgs()['_variable']);
        $loopName = $tag->getArgs()['_name'];

        $eachOutput = '';
        $view->setDontCacheVariable(true);

        $iteration = 0;
        if (is_string($loopVariable)){
            $loopVariable = [];
        }

        foreach ($loopVariable ?? [] as $key => $loop) {
            $eachOutput .= $content;
            if (isset($view->getLiveCacheVariableData()[$loopName])) {
                $view->addToLiveCacheVariableData($loopName, $loop);
            }

            $view->addToVariableData($loopName, $loop);
            $view->addToVariableData('_loop', [
                'iteration' => $iteration + 1,
                'index' => $iteration,
                'key' => $key,
            ]);
            $n = 0;

            foreach ($tag->getChildrenRecursive($tag) as $node) {
                $mode = $view->getModeRendererHandler($node->getTagName());

                if ($node->getTagName() === 'add_hook' && $iteration === 2){
                 //   dd($eachOutput, $this->getTonicsView()->accessArrayWithSeparator('dtRow'), $this->getTonicsView()->getModeStorage('add_hook'));
                }

                if ($mode instanceof TonicsModeRendererInterface) {
                    $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                    $eachOutput .= $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
                }
                ++$n;
            }
            ++$iteration;
        }

        return $eachOutput;
    }

    public function validateEach(array $explodedArgs)
    {
        $view = $this->getTonicsView();

        if (!isset($explodedArgs[0])) {
            $view->exception(\Exception::class, ['Each Requires a name']);
        }

        $arrayKeyLast = array_key_last($explodedArgs);
        if ($arrayKeyLast === 0) {
            $view->exception(\Exception::class, ['Each Requires a variable']);
        }
    }
}