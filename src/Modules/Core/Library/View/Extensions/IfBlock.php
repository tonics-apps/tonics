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
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateModeError;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class IfBlock extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        $result = false;
        if ($view->validateMaxArg($tagToken->getArg(), 'ifBlock', 100, 2)) {
            $result = true;
        }

        // This checks for odd arg num, which ifBlock should never have
        if (count($tagToken->getArg()) & 1){
            $this->error = "ifBlock Args Should be In The Form [[ifBlock('block-name-1', 'render', 'bloc-name-2', 'render', '...', '...')]]. In Two Steps";
            $result = false;
        }

        foreach ($tagToken->getArg() as $arg){
            if (!$view->getContent()->isBlock($arg)){
                $view->exception(TonicsTemplateModeError::class, [" `$arg` Is Not a Known Block"]);
            }
        }

        return $result;
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent('ifBlock', $tagToken->getContent(), $tagToken->getArg());
    }

    public function error(): string
    {
        return $this->error;
    }

    /**
     * @param string $content
     * @param array $args
     * @return string
     * @throws \Exception
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $skip = null; $view = $this->getTonicsView();
        foreach ($args as $k => $arg){
            if ($k === $skip){
                continue;
            }
            if (helper()->stripWhiteSpaces($view->renderABlock($arg))){
                return $view->renderABlock($args[++$k]);
            } else {
                $skip = ++$k;
            }
        }

        return '';
    }
}