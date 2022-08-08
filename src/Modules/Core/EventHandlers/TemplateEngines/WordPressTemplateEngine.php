<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressShortCode;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressWPContentURL;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class WordPressTemplateEngine implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        $wordpressShortCode = new WordPressShortCode();
        $wordpressWPContentURL = new WordPressWPContentURL();
        $event->addTemplateEngine('WordPressShortCode', $wordpressShortCode->getView());
        $event->addTemplateEngine('WordPressWPContentURL', $wordpressWPContentURL->getView());
    }
}