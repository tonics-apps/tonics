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
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;
use JetBrains\PhpStorm\NoReturn;

/**
 * RedirectAuthenticated class checks if user is authenticated, if so, identifies the user type and redirects
 * to their respective dashboard page
 */
class RedirectAuthenticated  extends SimpleState implements TonicsRouterRequestInterceptorInterface
{
    private OnRequestProcess $request;

    # States For RedirectAuthenticated
    const RedirectAuthenticatedInitialStateHandler = 'RedirectAuthenticatedInitialStateHandler';
    const RedirectAuthenticatedIdentifyUserType = 'RedirectAuthenticatedIdentifyUserType';

    const RedirectAuthenticatedUserTypeAdmin = 'RedirectAuthenticatedUserTypeAdmin';
    const RedirectAuthenticatedUserTypeCustomer = 'RedirectAuthenticatedUserTypeCustomer';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        $this->request = $request;
        // Initial State
        $this->setCurrentState(self::RedirectAuthenticatedInitialStateHandler);
        $this->runStates();
    }

    /**
     * @throws \Exception
     */
    public function RedirectAuthenticatedInitialStateHandler(): string
    {
        if (UserData::isAuthenticated()){
            return $this->switchState(self::RedirectAuthenticatedIdentifyUserType, self::NEXT);
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function RedirectAuthenticatedIdentifyUserType(): string
    {
        $userTable = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_UserTable);

        try {
            $table = Tables::getTable($userTable);
            if (Tables::getTable(Tables::USERS) === $table){
                return $this->switchState(self::RedirectAuthenticatedUserTypeAdmin, self::NEXT);
            }
            if (Tables::getTable(Tables::CUSTOMERS) === $table){
                return $this->switchState(self::RedirectAuthenticatedUserTypeCustomer, self::NEXT);
            }
            return $this->authError();
        } catch (\Exception){
            // Log...
            return $this->authError();
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function RedirectAuthenticatedUserTypeAdmin(): string
    {
        $redirectTo = UserData::USER_REDIRECTION_ADMIN_PAGE()[UserData::ADMIN_REDIRECTION_NAME];
        if (session()->hasValue(Session::SessionCategories_URLReferer)){
            $redirectTo = session()->retrieve(Session::SessionCategories_URLReferer, jsonDecode: true);
            session()->delete(Session::SessionCategories_URLReferer);
        }

        ## Else... Redirect to UserAdminDashboard
        RefreshTreeSystem::RefreshTreeSystem();
        redirect($redirectTo, 200);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function RedirectAuthenticatedUserTypeCustomer(): string
    {
        $redirectTo = UserData::USER_REDIRECTION_ADMIN_PAGE()[UserData::CUSTOMER_REDIRECTION_NAME];
        if (session()->hasValue(Session::SessionCategories_URLReferer)){
            $redirectTo = session()->retrieve(Session::SessionCategories_URLReferer, jsonDecode: true);
            session()->delete(Session::SessionCategories_URLReferer);
        }

        ## Else... Redirect to UserCustomerDashboard
        redirect($redirectTo, 200);
    }

    /**
     * @return OnRequestProcess
     */
    public function getRequest(): OnRequestProcess
    {
        return $this->request;
    }

    public function authError(): string
    {
        $this->setErrorCode(self::ERROR_UNAUTHORIZED_ACCESS__CODE)
            ->setErrorMessage(self::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        return self::ERROR;
    }
}