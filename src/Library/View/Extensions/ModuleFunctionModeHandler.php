<?php

namespace App\Library\View\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateModeError;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

/**
 * ModuleFunction is Similar to FunctionMode in That it Gives you access to modify block tag, unlike FunctionMode That Supports
 * only numerical argument, ModuleFunction is associative, here is an example:
 *
 * ```
 * [[b('block-name')
 *  This is [[arg('name')]], and he is [[arg('age')]] years old
 * ]]
 * ```
 * You can then call it like so in your template:
 *
 * ```
 * [[mFunc('block-name')
 *      [[arg('name' 'Devsrealm')]]
 *      [[arg('age' '100)]]
 * ]]
 * ```
 *
 * which gives you: => `This is Devsrealm, and he is 100 years old`
 *
 * If you want to use a block in the arg value you use bArg:
 *
 *  ```
 * [[mFunc('block-naee')
 *      [[bArg('name' 'all-names')]]
 *      [[arg('age' '100')]]
 * ]]
 * ```
 * Which means replace the name arg in block-name with the contents of block 'all-names'
 */
class ModuleFunctionModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'ModuleFunction');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $blockName = $tagToken->getArg()[0];
        $innerMFuncArg = [];
        if ($tagToken->hasChildren()){
            foreach ($tagToken->getChildren() as $child){
                /**@var $child Tag */
                if ($this->isArg($child->getTagName())){
                    if (count($child->getArgs()) !== 2){
                        $view->exception(TonicsTemplateModeError::class, ["Invalid Number of Args in Args of ModuleFunction, 2 allowed"]);
                    }
                    $innerMFuncArg[$blockName][] = [
                        'argType' => 'arg',
                        'key' => $child->getArgs()[0],
                        'value' => $child->getArgs()[1]
                    ];
                }

                if ($this->isBlockArg($child->getTagName())){
                    if (count($child->getArgs()) !== 2){
                        $view->exception(TonicsTemplateModeError::class, ["Invalid Number of Args in BlockArgs of ModuleFunction, 2 allowed"]);
                    }
                    if ($view->getContent()->isBlock($child->getArgs()[1])){
                        $innerMFuncArg[$blockName][] = [
                            'argType' => 'bArg',
                            'key' => $child->getArgs()[0],
                            'value' => $child->getArgs()[1]
                        ];
                    }
                }
            }
        }
        $view->getContent()->addToContent('mfunc', '', $innerMFuncArg);
    }

    public function error(): string
    {
        return $this->error;
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $view = $this->getTonicsView();
        $blockName = array_key_first($args);
        if (!is_string($blockName)){
            return '';
        }
        $args= array_shift($args);
        $renderedArgs = [];
        foreach ($args as $arg){
            if (isset($arg['argType'])){
                if ($this->isArg($arg['argType'])){
                    $renderedArgs[$arg['key']] = $arg['value'];
                }

                if ($this->isBlockArg($arg['argType'])){
                    $renderedArgs[$arg['key']] = $view->renderABlock($arg['value']);
                }
            }
        }

        $data = '';
        if ($view->getContent()->isBlock($blockName)){
            $blockContents = $view->getContent()->getBlock($blockName);
            foreach ($blockContents as $content) {
                $mode = $view->getModeRendererHandler($content['Mode']);
                if ($content['Mode'] === 'arg') {
                    if (key_exists($content['args'][0], $renderedArgs)) {
                        $data .= $renderedArgs[$content['args'][0]];
                        continue;
                    }
                    // If args doesn't exist, continue anyway
                    continue;
                }

                $view->setCurrentRenderingContentMode($content['Mode']);
                $node = (isset($content['nodes'])) ? $content['nodes'] : [];
                $data .= $mode->render($content['content'], $content['args'], $node);
            }
        }
        return $data;
    }

    /**
     * @param string $v
     * @return bool
     */
    public function isFunc(string $v): bool
    {
        return $v === 'mFunc' || $v === 'mfunc';
    }

    /**
     * @param string $v
     * @return bool
     */
    public function isArg(string $v): bool
    {
        $v = strtolower($v);
        return $v === 'arg';
    }

    /**
     * @param string $v
     * @return bool
     */
    public function isBlockArg(string $v): bool
    {
        $v = strtolower($v);
        return $v === 'barg';
    }

    /**
     * @param string $v
     * @return bool
     */
    public function isMFuncArg(string $v): bool
    {
        $v = strtolower($v);
        return $v === 'mfunc';
    }

}