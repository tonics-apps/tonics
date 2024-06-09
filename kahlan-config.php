<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

use Devsrealm\TonicsRouterSystem\Container\Container;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsRouterSystem\RequestInput;
use Devsrealm\TonicsRouterSystem\Resolver\RouteResolver;
use Devsrealm\TonicsRouterSystem\Response;
use Devsrealm\TonicsRouterSystem\Route;
use Devsrealm\TonicsRouterSystem\RouteNode;
use Devsrealm\TonicsRouterSystem\RouteTreeGenerator;
use Devsrealm\TonicsRouterSystem\State\RouteTreeGeneratorState;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\Tokenizer\State\DefaultTokenizerState;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Kahlan\Filter\Filters;

$tonicsRouterApply = function ($next) {
    $scope = $this->suite()->root()->scope(); // The top most describe scope.

    define('APP_ROOT', __DIR__);

    #-----------------------------------
    # FOR TONICS ROUTER SYSTEM
    #----------------------------

    class RouteSetup
    {

        public function wireRouter (): Router
        {
            ## Router And Request
            $routeTreeGeneratorState = new RouteTreeGeneratorState();
            $routeNode = new RouteNode();
            $onRequestProcess = new OnRequestProcess(new RouteResolver(new Container()), new Route(new RouteTreeGenerator($routeTreeGeneratorState, $routeNode)));

            return new Router($onRequestProcess,
                $onRequestProcess->getRouteObject(),
                new Response($onRequestProcess, new RequestInput()));
        }
    }

    $routeSetup = new RouteSetup();
    $scope->router = $routeSetup;
    $scope->helper = new Devsrealm\TonicsHelpers\TonicsHelpers();

    return $next();
};
$tonicsTemplateSystemApply = function ($next) {
    $scope = $this->suite()->root()->scope(); // The top most describe scope.
    $html = <<<EOD
[[import("module1") ]]
<html>
    <head>
        <title>App Name - [[v('title')]]</title>
    </head>
    <body>
        <div class="container">
            [[use("content")]]
        </div>
    </body>
</html>
EOD;
    $module1 = <<<EOD
[[b('content')
    <p>This is my body content.</p>
]]
EOD;
    $arrayTemplates = [
        'main'    => $html,
        'module1' => $module1,
    ];
    $scope->arrayTemplates = $arrayTemplates;
    $arrayLoader = new TonicsTemplateArrayLoader($arrayTemplates);
    $scope->arrayLoader = $arrayLoader;

    #
    # TonicsView
    #
    $settings = [
        'templateLoader' => $arrayLoader,
        'tokenizerState' => new DefaultTokenizerState(),
        'data'           => [
            'title'  => 'Fancy Value 55344343',
            'title2' => 'Fancy Value 2',
            'title3' => 'Fancy Value 3',
            'new'    => 'This is the new',
            'vary'   => [
                'in' => [
                    'in' => '<script type="text/javascript" src="js/test/fm.js"></script>',
                ],
            ],
        ],
        'content'        => new Content(),
    ];
    $view = new TonicsView($settings);
    $view->setTemplateName('main')->setDebug(false);
    $scope->view = $view;
    return $next();
};

Filters::apply($this, 'run', $tonicsRouterApply);
Filters::apply($this, 'run', $tonicsTemplateSystemApply);