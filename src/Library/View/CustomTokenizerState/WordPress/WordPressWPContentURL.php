<?php

namespace App\Library\View\CustomTokenizerState\WordPress;

use App\Library\View\CustomTokenizerState\WordPress\Extensions\WordPressWPContentURL\URL;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Devsrealm\TonicsTemplateSystem\Tree\Mode\Handlers\CharacterModeHandler;

class WordPressWPContentURL
{
    private TonicsView|null $init = null;

    public function __construct()
    {
        ## Tonics View
        $templateLoader = new TonicsTemplateArrayLoader();
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new WordPressWPContentURLTokenizerState(),
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
                        if ($mode instanceof TonicsModeRendererInterface) {
                            $modeOutput .= $mode->render($tag->getContent(), $tag->getArgs());
                        }
                    }catch (TonicsTemplateRangeException){
                    }
                }
                $tv->reset();
                return $modeOutput;
            }
        ];
        $view = new TonicsView($settings);
        // clear in-built mode handler
        $view->setModeHandler([]);
        $view->addModeHandler('url', URL::class);
        $view->addModeHandler('char', CharacterModeHandler::class);
        $this->init = $view;
    }

    public function getView(): ?TonicsView
    {
        return $this->init;
    }
}