<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\Audio;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\Caption;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\Character;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\DMCodeSnippet;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\EasyMediaDownload;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\TOC;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\Video;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateHandleEOF;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressShortCode
{
    private TonicsView|null $init = null;

    public function __construct()
    {
        ## Tonics View
        $templateLoader = new TonicsTemplateArrayLoader();
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new WordPressShortCodeTokenizerState(),
            'content' => new Content(),
            'handleEOF' => new WordPressShortCodeHandleEOF(),
            'render' => new WordPressShortCodeCustomRenderer()
        ];
        $view = new TonicsView($settings);
        // clear in-built mode handler
        $view->setModeHandler([]);
        $view->addModeHandler('char', Character::class);
        $view->addModeHandler('caption', Caption::class);
        $view->addModeHandler('dm_code_snippet', DMCodeSnippet::class);
        $view->addModeHandler('audio', Audio::class);
        $view->addModeHandler('video', Video::class);
        $view->addModeHandler('toc', TOC::class);
        $view->addModeHandler('easy_media_download', EasyMediaDownload::class);
        $view->addModeHandler('maxbutton', EasyMediaDownload::class);
        $this->init = $view;
    }

    public function getView(): ?TonicsView
    {
        return $this->init;
    }
}

