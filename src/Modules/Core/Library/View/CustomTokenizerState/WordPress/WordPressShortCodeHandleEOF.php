<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateHandleEOF;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressShortCodeHandleEOF implements TonicsTemplateHandleEOF
{

    public function handleEOF(TonicsView $tonicsView): void
    {
        $tonicsView->getTokenizerState()::finalEOFStackSort($tonicsView);
    }
}