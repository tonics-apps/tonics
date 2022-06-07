<?php

namespace App\Library\View\CustomTokenizerState\WordPress;

use App\InitLoader;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\Audio;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\Caption;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\Character;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\DMCodeSnippet;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\EasyMediaDownload;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\TOC;
use App\Library\View\CustomTokenizerState\WordPress\Extensions\Video;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
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
            'handleEOF' => function(TonicsView $tv){
                $tv->getTokenizerState()::finalEOFStackSort($tv);
            },
            'render' => function(TonicsView $tv){
                $modeOutput = '';
                /**@var Tag $tag */
                foreach ($tv->getStackOfOpenTagEl() as $tag){
                    try {
                        $mode = $tv->getModeRendererHandler($tag->getTagName());
                        if ($mode instanceof TonicsModeRenderWithTagInterface) {
                            $modeOutput .= $mode->render($tag->getContent(), helper()->mergeKeyIntersection($mode->defaultArgs(), $tag->getArgs()), $tag);
                        }
                    }catch (TonicsTemplateRangeException){
                    }
                }
                // $tv->reset();
                return $modeOutput;
            }
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