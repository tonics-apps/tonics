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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\View\Extensions\CombineModeHandler;
use App\Modules\Core\Library\View\Extensions\CSRFModeHandler;
use App\Modules\Core\Library\View\Extensions\EachLoop;
use App\Modules\Core\Library\View\Extensions\Events;
use App\Modules\Core\Library\View\Extensions\Hook;
use App\Modules\Core\Library\View\Extensions\IfBlock;
use App\Modules\Core\Library\View\Extensions\IfCondition;
use App\Modules\Core\Library\View\Extensions\MenuModeHandler;
use App\Modules\Core\Library\View\Extensions\ModuleFunctionModeHandler;
use App\Modules\Core\Library\View\Extensions\QueryModeHandler;
use App\Modules\Core\Library\View\Extensions\SessionView;
use App\Modules\Core\Library\View\Extensions\SQLSelectModeHandler;
use App\Modules\Core\Library\View\Extensions\StringFunctions;
use App\Modules\Core\Library\View\Extensions\TriggerBlockOnTheFly;
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
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event TonicsTemplateEngines */
        ## Tonics View
        $templateLoader = new TonicsTemplateFileLoader('html');
        $templateLoader->resolveTemplateFiles(AppConfig::getModulesPath());
        $templateLoader->resolveTemplateFiles(AppConfig::getAppsPath());
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
        $view->addModeHandler('combine_app', CombineModeHandler::class);
        $view->addModeHandler('combine_module', CombineModeHandler::class);

        $view->addModeHandler('mFunc', ModuleFunctionModeHandler::class);
        $view->addModeHandler('bArg', ModuleFunctionModeHandler::class);

        $view->addModeHandler('session', SessionView::class);
        $view->addModeHandler('__session', SessionView::class);

        $view->addModeHandler('event', Events::class);
        $view->addModeHandler('__event', Events::class);

        $view->addModeHandler('if', IfCondition::class, false);

        $view->addModeHandler('each', EachLoop::class, false);
        $view->addModeHandler('foreach', EachLoop::class, false);

        $view->addModeHandler('ifBlock', IfBlock::class);

        $view->addModeHandler('trigger_block', TriggerBlockOnTheFly::class);

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

        // HOOK
        $view->addModeHandler('add_hook', Hook::class);
        $view->addModeHandler('add_placeholder', Hook::class); // alias of add_hook
        $view->addModeHandler('add_hook_after', Hook::class);
        $view->addModeHandler('add_placeholder_after', Hook::class); // alias of add_placeholder_after
        $view->addModeHandler('add_hook_before', Hook::class);
        $view->addModeHandler('add_placeholder_before', Hook::class); // alias of add_placeholder_after

        $view->addModeHandler('reset_hook', Hook::class);
        $view->addModeHandler('reset_placeholder', Hook::class); // alias of reset_hook

        $view->addModeHandler('reset_all_hooks', Hook::class);
        $view->addModeHandler('reset_all_placeholder', Hook::class); // alias of reset_all_hooks

        $view->addModeHandler('hook_into', Hook::class);
        $view->addModeHandler('place_into', Hook::class); // alias of hook_into

        // SQL_SELECT
        $view->addModeHandler('sql', SQLSelectModeHandler::class);
        $view->addModeHandler('select', SQLSelectModeHandler::class, false);
        $view->addModeHandler('from', SQLSelectModeHandler::class);
        $view->addModeHandler('cols', SQLSelectModeHandler::class);
        $view->addModeHandler('cols', SQLSelectModeHandler::class);
        $view->addModeHandler('col_as', SQLSelectModeHandler::class);
        $view->addModeHandler('join', SQLSelectModeHandler::class);
        $view->addModeHandler('inner_join', SQLSelectModeHandler::class);
        $view->addModeHandler('left_join', SQLSelectModeHandler::class);
        $view->addModeHandler('right_join', SQLSelectModeHandler::class);
        $view->addModeHandler('where', SQLSelectModeHandler::class, false);
        // Operator
        $view->addModeHandler('op', SQLSelectModeHandler::class);
        // others
        $view->addModeHandler('order', SQLSelectModeHandler::class);
        $view->addModeHandler('keyword', SQLSelectModeHandler::class);
        // common functions
        $view->addModeHandler('sqlFunc', SQLSelectModeHandler::class);
        $view->addModeHandler('param', SQLSelectModeHandler::class);
        $view->addModeHandler('sql_block', SQLSelectModeHandler::class);
        $view->addModeHandler('reuse_sql', SQLSelectModeHandler::class);

        $view->addModeHandler('query', QueryModeHandler::class);
        $event->addTemplateEngine('Native', $view);
    }
}