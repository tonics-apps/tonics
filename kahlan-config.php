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
use Kahlan\Filter\Filters;

Filters::apply($this, 'run', function ($next) {
    $scope = $this->suite()->root()->scope(); // The top most describe scope.

    define('APP_ROOT', __DIR__);

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
});