<?php

namespace App\Modules\Core\RequestInterceptor;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;


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
           // set the current url to session here, just in case we wanna redirect to intended after loggin in
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
}