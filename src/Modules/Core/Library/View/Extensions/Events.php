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
 * Events gives access to the `event()->dispatch()` function in view
 *
 * You can use it as follows:
 * `[[event('App\Modules\Core\Events\OnEventName', 'eventFunction')]]`
 * <br>
 * which translates to: `event()->dispatch(new OnEventName())->eventFunction()`
 *
 * The first argument should be the fully-qualified event class name. and
 * you don't have to pass an eventFunction if there isn't any.
 *
 * <br>
 * Note: If the event result is not something you can output, use:
 * `[[__event('App\Modules\Core\Events\OnEventName', 'eventFunction')]]`
 * <br>
 * Yh, prefix the event with double under-score
 */
class Events extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'event',  2);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $tagName = $tagToken->getTagName();
        if ($tagName === 'event'){
            $view->getContent()->addToContent('event', '', $tagToken->getArg());
        }

        if ($tagName === '__event'){
            $view->getContent()->addToContent('__event', '', $tagToken->getArg());
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
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $view = $this->getTonicsView();
        $eventObject = container()->get(array_shift($args));
        $currentRenderingMode = $view->getCurrentRenderingContentMode();
        $result = call_user_func_array(array(event(), 'dispatch'), [$eventObject]);

        $eventObjectFunc = array_shift($args);
        if ($eventObjectFunc){
            $result = $result->$eventObjectFunc();
        }

        if ($currentRenderingMode === '__event'){
            return '';
        }

        if (!is_string($result)){
            return '';
        }

        return $result;

    }
}