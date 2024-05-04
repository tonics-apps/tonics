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
 * <br>
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
     * @throws \Exception
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

        $iteration = 0;
        if (is_string($loopVariable)){
            $loopVariable = [];
        }

        if (is_object($loopVariable)){
          $loopVariable = (array)$loopVariable;
        }

        $lastKey = array_key_last($loopVariable ?? []);
        foreach ($loopVariable ?? [] as $key => $loop) {
            $eachOutput .= $content;

            $view->addToVariableData($loopName, $loop);

            $loopInfo = [
                'first' => $iteration === 0,
                'last' => $key === $lastKey,
                'iteration' => $iteration + 1,
                'index' => $iteration,
                'key' => $key,
            ];
            $view->addToVariableData('_loop', $loopInfo);
            $view->addToVariableData($loopName.'_loop', $loopInfo);

            $n = 0;
            foreach ($tag->getChildrenRecursive($tag) as $node) {
                $mode = $view->getModeRendererHandler($node->getTagName());

                if ($mode instanceof TonicsModeRendererInterface) {
                    $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                    $eachOutput .= $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
                }

                helper()->garbageCollect(); # temporary solution to reduce memory increment in larger each loops.
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