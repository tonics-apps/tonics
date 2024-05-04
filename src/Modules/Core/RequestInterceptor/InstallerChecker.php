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

namespace App\Modules\Core\RequestInterceptor;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\IsAppInstalled;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;
use Symfony\Component\Yaml\Tests\A;

/**
 * The InstallerChecker Interceptor would only let the request pass if the app hasn't been installed,
 * otherwise, you get a simple looking error page that the app has already been installed.
 */
class InstallerChecker implements TonicsRouterRequestInterceptorInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
       $urlPath = $request->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode()->getFullRoutePath();
       if (AppConfig::TonicsIsReady() === false){
           return;
       }
       $isAppInstalled = new IsAppInstalled();
       # Meaning the app hasn't been installed


       if ($isAppInstalled->getStateResult() === SimpleState::ERROR){
           // if it has an error and an error code of 200, it means things are okay, except that we do not have the required table,
           // so, return to give chance for installation
           if ($isAppInstalled->getErrorCode() === 200){
               return;
           } else {
               ## For API
               if (str_starts_with($urlPath, '/api') || str_starts_with($urlPath, 'api')){
                   SimpleState::displayErrorMessage($isAppInstalled->getErrorCode(), $isAppInstalled->getErrorMessage(), true);
               }
               ## For Non-API
               SimpleState::displayErrorMessage($isAppInstalled->getErrorCode(), $isAppInstalled->getErrorMessage());
           }
       }

        # Anything Else Probably mean the app is installed
        $msg = "It Seems Tonic is Already Installed";
       ## For API
       if (str_starts_with($urlPath, '/api') || str_starts_with($urlPath, 'api')){
           SimpleState::displayErrorMessage(SimpleState::ERROR_APP_ALREADY_INSTALLED__CODE, $msg, true);
       }
        ## For Non-API
        SimpleState::displayErrorMessage(SimpleState::ERROR_APP_ALREADY_INSTALLED__CODE, $msg);
    }
}