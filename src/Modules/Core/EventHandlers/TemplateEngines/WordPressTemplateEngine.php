<?php

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