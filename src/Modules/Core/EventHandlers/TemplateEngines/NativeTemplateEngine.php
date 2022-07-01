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
use App\Modules\Core\Library\View\Extensions\StringFunctions;
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

        # String Functions
        $view->addModeHandler('string_addslashes', StringFunctions::class);
        $view->addModeHandler('string_chop', StringFunctions::class);
        $view->addModeHandler('string_html_entity_decode', StringFunctions::class);
        $view->addModeHandler('string_htmlentities', StringFunctions::class);
        $view->addModeHandler('string_htmlspecialchars_decode', StringFunctions::class);
        $view->addModeHandler('string_htmlspecialchars', StringFunctions::class);
        $view->addModeHandler('string_implode', StringFunctions::class);
        $view->addModeHandler('string_join', StringFunctions::class);
        $view->addModeHandler('string_lcfirst', StringFunctions::class);
        $view->addModeHandler('string_trim', StringFunctions::class);
        $view->addModeHandler('string_ltrim', StringFunctions::class);
        $view->addModeHandler('string_rtrim', StringFunctions::class);
        $view->addModeHandler('string_nl2br', StringFunctions::class);
        $view->addModeHandler('string_number_format', StringFunctions::class);
        $view->addModeHandler('string_sprintf', StringFunctions::class);
        $view->addModeHandler('string_str_ireplace', StringFunctions::class);
        $view->addModeHandler('string_str_replace', StringFunctions::class);
        $view->addModeHandler('string_str_pad', StringFunctions::class);
        $view->addModeHandler('string_str_repeat', StringFunctions::class);
        $view->addModeHandler('string_str_shuffle', StringFunctions::class);
        $view->addModeHandler('string_strip_tags', StringFunctions::class);
        $view->addModeHandler('string_stripcslashes', StringFunctions::class);
        $view->addModeHandler('string_strrev', StringFunctions::class);
        $view->addModeHandler('string_strtolower', StringFunctions::class);
        $view->addModeHandler('string_strtoupper', StringFunctions::class);
        $view->addModeHandler('string_substr', StringFunctions::class);
        $view->addModeHandler('string_ucfirst', StringFunctions::class);
        $view->addModeHandler('string_ucwords', StringFunctions::class);
        $view->addModeHandler('string_wordwrap', StringFunctions::class);

        $event->addTemplateEngine('Native', $view);
    }
}