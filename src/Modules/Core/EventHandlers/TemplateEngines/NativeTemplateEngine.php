<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
use App\Modules\Core\Library\View\Extensions\OnHookIntoEvent;
use App\Modules\Core\Library\View\Extensions\SessionView;
use App\Modules\Core\Library\View\Extensions\SetModeHandler;
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
        $s = DIRECTORY_SEPARATOR;
        $templateLoader = new TonicsTemplateFileLoader('html', [AppConfig::getModulesPath() . "{$s}Core{$s}Library{$s}Composer"]);
        $templateLoader->resolveTemplateFiles(AppConfig::getModulesPath());
        $templateLoader->resolveTemplateFiles(AppConfig::getAppsPath());
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new DefaultTokenizerState(),
            'templateCache' => new TonicsTemplateApcuCache(),
            'content' => new Content()
        ];
        $view = new TonicsView($settings);
        $view->addModeHandler('set', SetModeHandler::class);
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
        $view->addModeHandler('on_hook_into_event', OnHookIntoEvent::class);

        $event->addTemplateEngine('Native', $view);
    }
}