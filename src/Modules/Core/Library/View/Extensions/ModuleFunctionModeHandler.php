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

use App\Modules\Core\Library\View\Extensions\Traits\TonicsTemplateSystemHelper;
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
    use TonicsTemplateSystemHelper;

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
                    $argValueResolved = $this->resolveArgs('arg', [$child->getArgs()[1]])['arg'];
                    $innerMFuncArg[$blockName][] = [
                        'argType' => 'arg',
                        'key' => $child->getArgs()[0],
                        'value' => $argValueResolved
                    ];
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
                $expanded = $this->expandArgs($arg['value']);
                $renderedArgs[$arg['key']] = $expanded[0];
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