<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressWPContentURLCustomRenderer implements TonicsTemplateCustomRendererInterface
{

    public function render(TonicsView $tonicsView): string
    {
        $modeOutput = '';
        /**@var Tag $tag */
        foreach ($tonicsView->getStackOfOpenTagEl() as $tag) {
            try {
                $mode = $tonicsView->getModeRendererHandler($tag->getTagName());
                if ($mode instanceof TonicsModeRendererInterface) {
                    $modeOutput .= $mode->render($tag->getContent(), $tag->getArgs());
                }
            } catch (TonicsTemplateRangeException) {
            }
        }
        $tonicsView->reset();
        return $modeOutput;
    }
}