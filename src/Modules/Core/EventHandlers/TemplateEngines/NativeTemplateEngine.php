<?php

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\View\Extensions\CombineModeHandler;
use App\Modules\Core\Library\View\Extensions\CSRFModeHandler;
use App\Modules\Core\Library\View\Extensions\EachLoop;
use App\Modules\Core\Library\View\Extensions\Events;
use App\Modules\Core\Library\View\Extensions\IfBlock;
use App\Modules\Core\Library\View\Extensions\IfCondition;
use App\Modules\Core\Library\View\Extensions\MenuModeHandler;
use App\Modules\Core\Library\View\Extensions\ModuleFunctionModeHandler;
use App\Modules\Core\Library\View\Extensions\SessionView;
use App\Modules\Core\Library\View\Extensions\URLModeHandler;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\Caching\TonicsTemplateApcuCache;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateLoaderError;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateFileLoader;
use Devsrealm\TonicsTemplateSystem\Tokenizer\State\DefaultTokenizerState;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class NativeTemplateEngine implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws TonicsTemplateLoaderError
     */
    public function handleEvent(object $event): void
    {
        /** @var $event TonicsTemplateEngines */
        ## Tonics View
        $templateLoader = new TonicsTemplateFileLoader('html');
        $templateLoader->resolveTemplateFiles(AppConfig::getModulesPath());
        $templateLoader->resolveTemplateFiles(AppConfig::getPluginsPath());
        $templateLoader->resolveTemplateFiles(AppConfig::getThemesPath());
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new DefaultTokenizerState(),
            'templateCache' => new TonicsTemplateApcuCache(),
            'content' => new Content()
        ];
        $view = new TonicsView($settings);
        $view->addModeHandler('url', URLModeHandler::class);
        $view->addModeHandler('csrf', CSRFModeHandler::class);
        $view->addModeHandler('menu', MenuModeHandler::class);
        $view->addModeHandler('combine', CombineModeHandler::class);
        $view->addModeHandler('mFunc', ModuleFunctionModeHandler::class);

        $view->addModeHandler('session', SessionView::class);
        $view->addModeHandler('__session', SessionView::class);

        $view->addModeHandler('event', Events::class);
        $view->addModeHandler('__event', Events::class);

        $view->addModeHandler('if', IfCondition::class);

        $view->addModeHandler('each', EachLoop::class);
        $view->addModeHandler('foreach', EachLoop::class);

        $view->addModeHandler('ifBlock', IfBlock::class);

        $event->addTemplateEngine('Native', $view);
    }
}