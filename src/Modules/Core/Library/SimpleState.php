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

namespace App\Modules\Core\Library;

use JetBrains\PhpStorm\NoReturn;

abstract class SimpleState
{
    const DONE                               = 'DONE';
    const NEXT                               = 'NEXT';
    const ERROR                              = 'ERROR';
    const ERROR_PAGE_NOT_FOUND__CODE         = 404;
    const ERROR_PAGE_NOT_FOUND__MESSAGE      = 'Page Not Found 🙄';
    const ERROR_UNAUTHORIZED_ACCESS__CODE    = 401;
    const ERROR_UNAUTHORIZED_ACCESS__MESSAGE = 'Unauthorized Access ⚠';
    const ERROR_FORBIDDEN__CODE              = 403;
    const ERROR_FORBIDDEN__MESSAGE           = 'Forbidden';
    const ERROR_TOKEN_MISMATCH__CODE         = 403;

    # ERROR MESSAGES:
    const ERROR_TOKEN_MISMATCH__MESSAGE        = 'Unauthorized Action 😮.';
    const ERROR_PAGE_IS_GONE__CODE             = 410;
    const ERROR_PAGE_IS_GONE__MESSAGE          = 'Page Is No Longer Available';
    const ERROR_APP_ALREADY_INSTALLED__CODE    = 200;
    const ERROR_APP_ALREADY_INSTALLED__MESSAGE = "It Seems App Is Already Installed";
    const ERROR_TOO_MANY_REQUEST__CODE         = 429;
    const ERROR_TOO_MANY_REQUEST__MESSAGE      = 'Too Many Request';
    private string $returnState    = '';
    private string $currentState   = '';
    private bool   $debug          = false;
    private int    $errorCode      = 0;
    private string $errorMessage   = '';
    private string $successMessage = '';
    private string $stateResult    = '';

    /**
     * If an error is encountered, it would render a html page if $returnErrorPage is set to true, otherwise,
     * it would break out of the states loop giving you the option
     * to handle it yourself by getting the $stateResult from the `getStateResult()` method
     *
     * @param bool $returnErrorPage
     *
     * @throws \Exception
     */
    public function runStates (bool $returnErrorPage = true): void
    {
        while ($stateResult = $this->dispatchState($this->currentState)) {
            $this->stateResult = $stateResult;
            if ($stateResult === self::NEXT) {
                continue;
            }

            # If state returns a done, then it probably means the state is done...we break out
            if ($stateResult === self::DONE) {
                break;
            }

            if ($stateResult === self::ERROR) {
                if ($returnErrorPage) {
                    $this->displayErrorMessage($this->errorCode, $this->errorMessage);
                } else {
                    break;
                }
            }
        }
    }

    /**
     * The difference between emitError() func and return an ERROR string is that, this func abruptly quit execution
     *
     * @param bool $returnErrorPage
     *
     * @throws \Exception
     */
    #[NoReturn] public function emitError (bool $returnErrorPage = true): void
    {
        if ($returnErrorPage) {
            $this->displayErrorMessage($this->errorCode, $this->errorMessage);
        }
        exit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public static function displayErrorMessage (int|string $errorCode, string $errorMessage, bool $isAPI = false): void
    {
        if (str_starts_with(request()->getRequestURL(), '/api')) {
            $isAPI = true;
        }

        if ($isAPI === false) {
            if (is_string($errorCode)) {
                $errorCode = 400;
            }
            http_response_code($errorCode);
            view('Modules::Core/Views/error-page', ['error-code' => $errorCode, 'error-message' => $errorMessage]);
        } else {
            response()->onError($errorCode, $errorMessage);
        }
        exit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public static function displayUnauthorizedErrorMessage (
        int|string $errorCode = self::ERROR_UNAUTHORIZED_ACCESS__CODE,
        string     $errorMessage = self::ERROR_UNAUTHORIZED_ACCESS__MESSAGE,
        bool       $isAPI = false): void
    {
        if (str_starts_with(request()->getRequestURL(), '/api')) {
            $isAPI = true;
        }
        self::displayErrorMessage($errorCode, $errorMessage, $isAPI);
    }

    /**
     * @param string $state
     *
     * @return mixed
     */
    public function dispatchState (string $state): mixed
    {
        return $this->$state();
    }


    /**
     * This switches the state:
     * You can use the $stateResult to affect the state machine by using self::NEXT, self::ERROR, self::DONE
     *
     * @param string $state
     * @param string|null $stateResult
     *
     * @return string|$this
     */
    public function switchState (string $state, string $stateResult = null): SimpleState|string
    {
        $this->setCurrentState($state);
        if ($this->debug) {
            print "State Switched To $state" . "<br>";
        }
        if ($stateResult !== null) {
            return $stateResult;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentState (): string
    {
        return $this->currentState;
    }

    /**
     * @param string $currentState
     *
     * @return SimpleState
     */
    public function setCurrentState (string $currentState): SimpleState
    {
        $this->currentState = $currentState;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnState (): string
    {
        return $this->returnState;
    }

    /**
     * @param string $returnState
     *
     * @return SimpleState
     */
    public function setReturnState (string $returnState): SimpleState
    {
        $this->returnState = $returnState;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug (): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug (bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @return int
     */
    public function getErrorCode (): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     *
     * @return SimpleState
     */
    public function setErrorCode (int $errorCode): SimpleState
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage (): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return SimpleState
     */
    public function setErrorMessage (string $errorMessage): SimpleState
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return string
     */
    public function getStateResult (): string
    {
        return $this->stateResult;
    }

    /**
     * @param string $stateResult
     */
    public function setStateResult (string $stateResult): void
    {
        $this->stateResult = $stateResult;
    }

    /**
     * @return string
     */
    public function getSuccessMessage (): string
    {
        return $this->successMessage;
    }

    /**
     * @param string $successMessage
     */
    public function setSuccessMessage (string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }


}