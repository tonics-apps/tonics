<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputInterface;
use Devsrealm\TonicsRouterSystem\Response;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use JetBrains\PhpStorm\NoReturn;

/**
 * This would return the session object, To do anything with session, you first need to start the session using: `session()->startSession()`,
 *
 * <br>
 * then you can go ahead and work on the session, e.g to add something to a session, you do: session()->write(['data']);
 * @return Session
 * @throws Exception
 */
function session(): Session
{
    return AppConfig::initLoaderMinimal()->getSession();
}

/**
 * Query request information, this is same as request() glob method
 *
 * @return OnRequestProcess
 * @throws Exception
 */
function url(): OnRequestProcess
{
    return request();
}


/**
 *
 * @return OnRequestProcess
 * @throws Exception
 */
function request(): OnRequestProcess
{
    return AppConfig::initLoaderOthers()->getRouter()->getOnRequestProcessingEvent();
}

/**
 * @return Response
 * @throws Exception
 */
function response(): Response
{
    return AppConfig::initLoaderOthers()->getRouter()->getResponse();
}

/**
 * @throws Exception
 */
function input(): TonicsRouterRequestInputInterface
{
    return AppConfig::initLoaderOthers()->getRouter()->getResponse()->getRequestInput();
}

/**
 * Redirect to $url
 * @param string $url
 * @param int|null $code
 * @throws Exception
 */
#[NoReturn] function redirect(string $url, ?int $code = null): void
{
    if ($code !== null) {
        response()->httpResponseCode($code);
    }
    response()->redirect($url);
}

/**
 * Route Retrieval. (Works For All route method except the match method)
 *
 * <br>
 * To get a route url, you pass in the name of the route, i.e, `media.show`.
 *
 * <br>
 * If the route contains a parameter, pass them in $parameter, for example, if the route is /api/:size/files/:page, you can pass the parameter
 * like so: route('media.show', ['size' => 10, 'page'=> 4]);, which gets you /api/10/files/4. An empty string is return if no route is founded.
 *
 * Note: Avoid using the same required parameter in route url if you are retrieving route using this function, having /api/:name/:name,
 * would replace both :name. You can use a numbered array to overcome that limitation.
 * @param string $name
 * @param array $parameters
 * @return string
 * @throws Exception
 */
function route(string $name,  array $parameters = []): string
{
    return AppConfig::initLoaderOthers()->getRouter()->getRoute()->getRouteTreeGenerator()->namedURL($name, $parameters);
}

/**
 * Helper function for file, module, migration and plugin related
 * @return TonicsHelpers
 * @throws Exception
 */
function helper(): TonicsHelpers
{
    return AppConfig::initLoaderMinimal()->getTonicsHelpers();
}

/**
 * ALIAS OF main `helper()` method
 * @return TonicsHelpers
 * @throws Exception
 */
function utility(): TonicsHelpers
{
    return helper();
}

/**
 * This gets a new instance of TonicsQuery,
 * if newConnection is set to true it creates a new PDO connection
 *
 * <br>
 *
 * If you pass a callable func in `$onGetDB`, you would get an instance of the db connection, and
 * it would be cleaned up or destroyed after you are done using it. A new call to it would throw an error, be warned.
 * This is a good option to keep unused connection closed and tidy up.
 * @param bool $newConnection
 * @param callable|null $onGetDB
 * @return TonicsQuery|null
 * @throws Exception
 */
function db(bool $newConnection = true, callable $onGetDB = null): ?TonicsQuery
{
    $db = AppConfig::initLoaderMinimal()->getDatabase(true)->Q();
    if ($onGetDB){
        $onGetDB($db);
        $db->getTonicsQueryBuilder()->destroyPdoConnection();
    } else {
        return $db;
    }

    return null;
}

/**
 * @throws Exception
 */
function table(): Tables
{
    return db()->getTonicsQueryBuilder()->getTables();
}

/**
 * @throws Exception
 */
function templateEngines(): TonicsTemplateEngines
{
    return AppConfig::initLoaderOthers()->getTonicsTemplateEngines();
}

/**
 * @throws Exception
 */
function job(string $transporterName = ''): Job
{
    if (!$transporterName){
        $transporterName = AppConfig::getJobTransporter();
    }
    return AppConfig::initLoaderOthers()::getJobEventDispatcher($transporterName);
}

/**
 * @throws Exception
 */
function schedule(string $transporterName = ''): Scheduler
{
    if (!$transporterName){
        $transporterName = AppConfig::getSchedulerTransporter();
    }
    return AppConfig::initLoaderOthers()::getScheduler($transporterName);
}

/**
 * @param string $key
 * @param $data
 * @return void
 * @throws Exception
 */
function addToGlobalVariable(string $key, $data): void
{
    AppConfig::initLoaderMinimal()::addToGlobalVariable($key, $data);
}

/**
 * @throws Exception
 */
function getPostData()
{
    return AppConfig::initLoaderMinimal()::getGlobalVariableData('Data') ?? [];
}

/**
 * @throws Exception
 */
function getGlobalVariableData()
{
    return AppConfig::initLoaderMinimal()::getGlobalVariable();
}

/**
 * For $condition, you can use:
 *
 * - `TonicsView::RENDER_CONCATENATE_AND_OUTPUT` if you want to concatenate and output to the browser (default)
 * - `TonicsView::RENDER_CONCATENATE` if you only want to concatenate and get the string output
 * - `TonicsView::RENDER_TOKENIZE_ONLY` if you only want to tokenize and get the view object
 *
 * Note: If you have a custom render, It won't respect $condition
 * @param string $viewname
 * @param array|stdClass $data
 * @param int $condition
 * @return mixed
 * @throws Exception
 */
function view(string $viewname, array|stdClass $data = [], int $condition = TonicsView::RENDER_CONCATENATE_AND_OUTPUT): mixed
{
    $data = [...$data, ...getGlobalVariableData()];
    $view = AppConfig::initLoaderOthers()->getTonicsView()->setVariableData($data);
    #
    # For Some reason, in CLI, if we call view multiple times, it renders hook_into content that number of times,
    # instead of just once, the below is a work-around until I find a fix
    #
    if (helper()->isCLI()){
        $newView = new TonicsView();
        $newView->setVariableData($data);
        $view->reset()->copySettingsToNewViewInstance($newView);
        $view = $newView;
    }
    return $view->render($viewname, $condition);
}

/**
 * Useful for automatically constructing class object graph.
 *
 * <br>
 * To resolve a class do: `container()->get(Classname::class);`
 *
 * <br>
 * For multiple class, do: `container()->resolveMany([Class::class, Class2::class]);`
 * @throws Exception
 */
function container(): Container
{
    return AppConfig::initLoaderMinimal()->getContainer();
}

/**
 * @throws Exception
 */
function event(): EventDispatcher
{
    return AppConfig::initLoaderOthers()->getEventDispatcher();
}

/**
 * @throws Exception
 */
function dom(): \Devsrealm\TonicsDomParser\DomParser
{
    return AppConfig::initLoaderMinimal()->getDomParser();
}