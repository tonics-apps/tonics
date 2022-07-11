<?php

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\CacheKeys;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\MyPDO;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputInterface;
use Devsrealm\TonicsRouterSystem\Response;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
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
 * @throws Exception
 */
function db(): MyPDO
{
    return AppConfig::initLoaderMinimal()->getDatabase();
}

/**
 * @throws Exception
 */
function templateEngines(): TonicsTemplateEngines
{
    return AppConfig::initLoaderOthers()->getTonicsTemplateEngines();
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
 * Load Base Template If Not Already Loaded
 * @throws Exception
 */
function loadTemplateBase(): void
{
    if (AppConfig::initLoaderMinimal()::globalVariableKeyExist('BASE_TEMPLATE') === false){
        addToGlobalVariable('BASE_TEMPLATE', view('Modules::Core/Views/Templates/theme', condition: TonicsView::RENDER_TOKENIZE_ONLY));
    }
}

/**
 * @return TonicsView|null
 * @throws Exception
 */
function getBaseTemplate(): TonicsView|null
{
    if (AppConfig::initLoaderMinimal()::globalVariableKeyExist('BASE_TEMPLATE') === false){
        loadTemplateBase();
    }
    return AppConfig::initLoaderMinimal()::getGlobalVariableData('BASE_TEMPLATE');
}

/**
 * @param string $cacheKey
 * @param callable|null $cacheNotFound
 * Callback to call if cache doesn't exist
 * @param callable|null $cacheFound
 * Callback to call if cache exist
 * @return void
 * @throws Exception
 */
function renderBaseTemplate(string $cacheKey = '', callable $cacheNotFound = null, callable $cacheFound = null): void
{
    if (!apcu_exists($cacheKey)){
        if ($cacheNotFound !== null){
            $cacheNotFound();
        }
    } else {
        $cacheData = apcu_fetch($cacheKey);
        getBaseTemplate()->setContent($cacheData['contents']);
        getBaseTemplate()->setModeStorages($cacheData['modeStorage'] ?? []);
        // reset query param
        if (isset($cacheData['variable']['URL'])){
            $cacheData['variable']['URL'] = [
                'REQUEST_URL' => url()->getRequestURL(),
                'PARAMS' => url()->getParams(),
                'REFERER' => url()->getReferer()
            ];
        }
        getBaseTemplate()->setVariableData($cacheData['variable']);
        if ($cacheFound !== null){
            $cacheFound();
        }
    }

    echo getBaseTemplate()->outputContentData(getBaseTemplate()->getContent()->getContents());
}

/**
 * @throws Exception
 */
function getPostData()
{
    return AppConfig::initLoaderMinimal()::getGlobalVariableData('Data') ?? [];
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
    $view = AppConfig::initLoaderOthers()->getTonicsView()->setVariableData($data);
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