<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;
use Devsrealm\TonicsRouterSystem\RequestMethods;

class CSRFGuard extends SimpleState implements TonicsRouterRequestInterceptorInterface
{
    const CSRFGuardInitialStateHandler = 'CSRFGuardInitialStateHandler';

    # States For CSRFGuard
    const CSRFGuardRequestTypePost = 'CSRFGuardRequestTypePost';
    const CSRFGuardTokenInSession = 'CSRFGuardTokenInSession';
    const CSRFGuardVerifyToken = 'CSRFGuardVerifyToken';
    private ?OnRequestProcess $request = null;

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(OnRequestProcess $request): void
    {
        $this->request = $request;

        // Initial State
        $this->setCurrentState(self::CSRFGuardInitialStateHandler);
        $this->runStates(false);
        // Token Mis-Match, send to login page
        if ($this->getStateResult() === self::ERROR) {
            session()->logout();
            Authenticated::handleUnAuthenticated();
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function CSRFGuardInitialStateHandler(): string
    {
        # If it has method: put, post, patch, delete (methods that deals with state changes)
        if (key_exists(request()->getRequestMethod(), RequestMethods::$requestTypesPost)) {
            $this->setCurrentState(self::CSRFGuardRequestTypePost);
            return self::NEXT;
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function CSRFGuardRequestTypePost(): string
    {
        if (input()->fromPost()->hasValue('token')) {
            $this->setCurrentState(self::CSRFGuardTokenInSession);
            return self::NEXT;
        }

        # or From an API Request
        if (is_array(request()->getAPIHeaderKey(['tonics_csrf_token'])) && key_exists('tonics_csrf_token', request()->getAPIHeaderKey(['tonics_csrf_token']))
            && !empty(request()->getAPIHeaderKey(['tonics_csrf_token'])['tonics_csrf_token'])) {
            $this->setCurrentState(self::CSRFGuardTokenInSession);
            return self::NEXT;
        }

        return $this->tokenMismatchError();
    }

    public function tokenMismatchError(): string
    {
        $this->setErrorCode(self::ERROR_TOKEN_MISMATCH__CODE)
            ->setErrorMessage(self::ERROR_TOKEN_MISMATCH__MESSAGE);
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function CSRFGuardTokenInSession(): string
    {
        if (session()->hasValue('tonics_csrf_token')) {
            $this->setCurrentState(self::CSRFGuardVerifyToken);
            return self::NEXT;
        }

        return $this->tokenMismatchError();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function CSRFGuardVerifyToken(): string
    {
        # Why checking this again, if we already did that in CSRFGuardRequestTypePost state?
        # well, since it is a state implementation, we might decide to initiate the state from anywhere,
        # so, we gatz make it as stateless as possible
        if (is_array(request()->getAPIHeaderKey(['tonics_csrf_token'])) && key_exists('tonics_csrf_token', request()->getAPIHeaderKey(['tonics_csrf_token']))
            && !empty(request()->getAPIHeaderKey(['tonics_csrf_token'])['tonics_csrf_token'])) {
            $inputToken = request()->getAPIHeaderKey(['tonics_csrf_token'])['tonics_csrf_token'];
        } else {
            $inputToken = input()->fromPost()->retrieve('token');
        }


        if (hash_equals(session()->retrieve('tonics_csrf_token', default: true, jsonDecode: true), $inputToken)) {
            return self::DONE;
        }

        return $this->tokenMismatchError();
    }

    /**
     * @return OnRequestProcess|null
     */
    public function getRequest(): ?OnRequestProcess
    {
        return $this->request;
    }
}