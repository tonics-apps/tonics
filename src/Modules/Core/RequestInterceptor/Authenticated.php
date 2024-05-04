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

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;
use JetBrains\PhpStorm\NoReturn;


/**
 * This simply checks if user is authenticated, if user is not auth, you get an error.
 *
 * Note: There are differences between authentication and authorization, so, you'll still need to do authorization in your respective controller
 */
class Authenticated implements TonicsRouterRequestInterceptorInterface
{


    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
       if (UserData::isAuthenticated() === false){
           self::handleUnAuthenticated();
       }
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public static function handleUnAuthenticated()
    {
        // set the current url to session here, just in case we wanna redirect to intended after log in
        session()->append(Session::SessionCategories_URLReferer, request()->getHeaderByKey('REQUEST_URI'));

        # If this is for admin, then redirect to admin login
        if (str_starts_with(request()->getRequestURL(), '/admin')){
            redirect(route('admin.login'));
        }

        # If this is for customer, then redirect to customer login
        if (str_starts_with(request()->getRequestURL(), '/customer')){
            redirect(route('customer.login'));
        }

        # Else...
        SimpleState::displayUnauthorizedErrorMessage();
    }
}