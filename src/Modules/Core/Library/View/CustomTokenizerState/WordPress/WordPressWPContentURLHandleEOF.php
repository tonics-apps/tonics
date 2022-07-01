<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateHandleEOF;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressWPContentURLHandleEOF implements TonicsTemplateHandleEOF
{

    public function handleEOF(TonicsView $tonicsView): void
    {
        $tonicsView->getTokenizerState()::finalEOFStackSort($tonicsView);
    }
}