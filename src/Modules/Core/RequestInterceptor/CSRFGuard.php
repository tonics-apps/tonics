<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\RequestInterceptor;

use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;
use Devsrealm\TonicsRouterSystem\RequestMethods;

class CSRFGuard extends SimpleState implements TonicsRouterRequestInterceptorInterface
{
    private ?OnRequestProcess $request = null;

    # States For CSRFGuard
    const CSRFGuardInitialStateHandler = 'CSRFGuardInitialStateHandler';
    const CSRFGuardRequestTypePost = 'CSRFGuardRequestTypePost';
    const CSRFGuardTokenInSession = 'CSRFGuardTokenInSession';
    const CSRFGuardVerifyToken = 'CSRFGuardVerifyToken';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        $this->request = $request;

        // Initial State
        $this->setCurrentState(self::CSRFGuardInitialStateHandler);
        $this->runStates(false);
        // Token Mis-Match, send to login page
        if ($this->getStateResult() === self::ERROR){
            session()->logout();
            Authenticated::handleUnAunthenticated();
        }
    }

    /**
     * @throws \Exception
     */
    public function CSRFGuardInitialStateHandler(): string
    {
        # If it has method: put, post, patch, delete (methods that deals with state changes)
        if (key_exists(request()->getRequestMethod(), RequestMethods::$requestTypesPost)){
            $this->setCurrentState(self::CSRFGuardRequestTypePost);
            return self::NEXT;
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function CSRFGuardRequestTypePost(): string
    {
        if (input()->fromPost()->hasValue('token')){
            $this->setCurrentState(self::CSRFGuardTokenInSession);
            return self::NEXT;
        }

        # or From an API Request
        if (is_array(request()->getAPIHeaderKey(['tonics_csrf_token'])) && key_exists('tonics_csrf_token', request()->getAPIHeaderKey(['tonics_csrf_token']))
            && !empty(request()->getAPIHeaderKey(['tonics_csrf_token'])['tonics_csrf_token'])){
            $this->setCurrentState(self::CSRFGuardTokenInSession);
            return self::NEXT;
        }

        return $this->tokenMismatchError();
    }

    /**
     * @throws \Exception
     */
    public function CSRFGuardTokenInSession(): string
    {
        if (session()->hasValue('tonics_csrf_token')){
            $this->setCurrentState(self::CSRFGuardVerifyToken);
            return self::NEXT;
        }

        return $this->tokenMismatchError();
    }

    /**
     * @throws \Exception
     */
    public function CSRFGuardVerifyToken(): string
    {
        # Why checking this again, if we already did that in CSRFGuardRequestTypePost state?
        # well, since it is a state implementation, we might decide to initiate the state from anywhere,
        # so, we gatz make it as stateless as possible
        if (is_array(request()->getAPIHeaderKey(['tonics_csrf_token'])) && key_exists('tonics_csrf_token', request()->getAPIHeaderKey(['tonics_csrf_token']))
            && !empty(request()->getAPIHeaderKey(['tonics_csrf_token'])['tonics_csrf_token'])){
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

    public function tokenMismatchError(): string
    {
        $this->setErrorCode(self::ERROR_TOKEN_MISMATCH__CODE)
            ->setErrorMessage(self::ERROR_TOKEN_MISMATCH__MESSAGE);
        return self::ERROR;
    }
}