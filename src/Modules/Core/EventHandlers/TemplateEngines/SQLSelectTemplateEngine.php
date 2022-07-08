<?php

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\View\Extensions\IfCondition;
use App\Modules\Core\Library\View\Extensions\SQLSelectModeHandler;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\Caching\TonicsTemplateApcuCache;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateFileLoader;
use Devsrealm\TonicsTemplateSystem\Tokenizer\State\DefaultTokenizerState;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class SQLSelectTemplateEngine implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event TonicsTemplateEngines */
        ## Tonics View
        $templateLoader = new TonicsTemplateFileLoader('html');
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new DefaultTokenizerState(),
            'templateCache' => new TonicsTemplateApcuCache(),
            'content' => new Content()
        ];
        $view = new TonicsView($settings);
        $view->addModeHandler('if', IfCondition::class, false);

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
        $view->addModeHandler('sql_func', SQLSelectModeHandler::class);

        $view->addModeHandler('param', SQLSelectModeHandler::class);

        $event->addTemplateEngine('SQLSelect', $view);
    }
}