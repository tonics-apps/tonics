<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressShortCodeCustomRenderer implements TonicsTemplateCustomRendererInterface
{

    public function render(TonicsView $tonicsView): string
    {
        $modeOutput = '';
        /**@var Tag $tag */
        foreach ($tonicsView->getStackOfOpenTagEl() as $tag) {
            try {
                $mode = $tonicsView->getModeRendererHandler($tag->getTagName());
                if ($mode instanceof TonicsModeRenderWithTagInterface) {
                    $modeOutput .= $mode->render($tag->getContent(), helper()->mergeKeyIntersection($mode->defaultArgs(), $tag->getArgs()), $tag);
                }
            } catch (TonicsTemplateRangeException) {
            }
        }
        // $tv->reset();
        return $modeOutput;
    }
}