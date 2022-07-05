<?php

namespace App\Modules\Core\Library\View\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

/**
 * Unlike the use mode handler that waits until the final rendering period to use a block, this Mode Handler
 * trigger a block on the fly.
 */
class TriggerBlockOnTheFly extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), 'trigger_block');
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $block_name = $tagToken->getFirstArgChild();
        $this->getTonicsView()->renderABlock($block_name);
    }

    public function error(): string
    {
        return '';
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $block_name = $args[0];
        $this->getTonicsView()->renderABlock($block_name);
        return '';
    }
}