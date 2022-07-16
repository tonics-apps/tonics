<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\RequestInterceptor;


use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
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

    private $userType = null;
    private $userTypeName = null;

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
            $this->setCurrentState(self::RedirectAuthenticatedIdentifyUserType);
            return self::NEXT;
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function RedirectAuthenticatedIdentifyUserType(): string
    {
        $userType = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_UserType);
        ## If user type doesn't exist, return unauthorized access
        if (!key_exists($userType, UserData::$USER_TABLES)){
            return $this->authError();
        }

        $userTypeName = UserData::$USER_TABLES[$userType];

        $this->userType = $userType;
        $this->userTypeName = $userTypeName;

        if($userTypeName === UserData::UserAdmin_STRING){
            $this->setCurrentState(self::RedirectAuthenticatedUserTypeAdmin);
            return self::NEXT;
        }

        if($userTypeName === UserData::UserCustomer_STRING){
            $this->setCurrentState(self::RedirectAuthenticatedUserTypeCustomer);
            return self::NEXT;
        }

        ## Anything Else: return unauthorized access (we can never get here, but we should use this just in case)
        return $this->authError();
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function RedirectAuthenticatedUserTypeAdmin(): string
    {
        if (session()->hasValue(Session::SessionCategories_URLReferer)){
            $redirectTo = session()->retrieve(Session::SessionCategories_URLReferer, jsonDecode: true);
            session()->delete(Session::SessionCategories_URLReferer);
            redirect($redirectTo, 200);
        }

        ## Else... Redirect to UserAdminDashboard
        redirect(UserData::USER_REDIRECTION_ADMIN_PAGE()[$this->userTypeName], 200);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function RedirectAuthenticatedUserTypeCustomer(): string
    {
        if (session()->hasValue(Session::SessionCategories_URLReferer)){
            $redirectTo = session()->retrieve(Session::SessionCategories_URLReferer, jsonDecode: true);
            session()->delete(Session::SessionCategories_URLReferer);
            redirect($redirectTo, 200);
        }

        ## Else... Redirect to UserCustomerDashboard
        redirect(UserData::USER_REDIRECTION_ADMIN_PAGE()[$this->userTypeName], 200);
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

    /**
     * @return null
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @return null
     */
    public function getUserTypeName()
    {
        return $this->userTypeName;
    }
}