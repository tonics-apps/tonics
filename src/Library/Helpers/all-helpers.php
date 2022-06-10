<?php

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\MyPDO;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputInterface;
use Devsrealm\TonicsRouterSystem\Response;
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
    return AppConfig::initLoader()->getSession();
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
    return AppConfig::initLoader()->getRouter()->getOnRequestProcessingEvent();
}

/**
 * @return Response
 * @throws Exception
 */
function response(): Response
{
    return AppConfig::initLoader()->getRouter()->getResponse();
}

/**
 * @throws Exception
 */
function input(): TonicsRouterRequestInputInterface
{
    return AppConfig::initLoader()->getRouter()->getResponse()->getRequestInput();
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
    return AppConfig::initLoader()->getRouter()->getRoute()->getRouteTreeGenerator()->namedURL($name, $parameters);
}

/**
 * Helper function for file, module, migration and plugin related
 * @return TonicsHelpers
 * @throws Exception
 */
function helper(): TonicsHelpers
{
    return AppConfig::initLoader()->getTonicsHelpers();
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
 * @throws Exception
 */
function db(): MyPDO
{
    return AppConfig::initLoader()->getDatabase();
}

/**
 * @param string $key
 * @param $data
 * @return void
 * @throws Exception
 */
function addToGlobalVariable(string $key, $data): void
{
    AppConfig::initLoader()::addToGlobalVariable($key, $data);
}

/**
 * @param string $viewname
 * @param array|stdClass $data
 * @throws Exception
 */
function view(string $viewname, array|stdClass $data = []): void
{
    $view = AppConfig::initLoader()->getTonicsView()->setVariableData($data);
    $view->render($viewname);
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
    return AppConfig::initLoader()->getContainer();
}

/**
 * @throws Exception
 */
function event(): EventDispatcher
{
    return AppConfig::initLoader()->getEventDispatcher();
}

/**
 * @throws Exception
 */
function dom(): \Devsrealm\TonicsDomParser\DomParser
{
    return AppConfig::initLoader()->getDomParser();
}