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
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

/**
 * SessionView gives access to the session function in view, you'll be able to call must function you can call with `session()`
 *
 * You can use it as follows:
 * `[[session('function-name', 'arg1', 'arg2')`]]
 *
 * <br>
 * Note: If the session function is not something you can output, use:
 * `[[__session('function-name', 'arg1', 'arg2')`]]`
 * <br>
 * Yh, prefix the session with double under-score
 */
class SessionView extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'session', 4);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $tagName = $tagToken->getTagName();
        if ($tagName === 'session'){
            $view->getContent()->addToContent('session', '', $tagToken->getArg());
        }

        if ($tagName === '__session'){
            $view->getContent()->addToContent('__session', '', $tagToken->getArg());
        }
    }

    public function error(): string
    {
        return $this->error;
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
        $view = $this->getTonicsView();
        $sessionFunc = array_shift($args);
        $currentRenderingMode = $view->getCurrentRenderingContentMode();


        $result = '';
        try {
            if (!empty($args)){
                $result = call_user_func_array(array(session(), $sessionFunc), $args);
            } else {
                $result = call_user_func(array(session(), $sessionFunc));
            }
        } catch (\Exception $exception){
            // Log..
        }

        if ($currentRenderingMode === '__session'){
            return '';
        }

        if ($result === null){
            return '';
        }
        return $result;
    }
}