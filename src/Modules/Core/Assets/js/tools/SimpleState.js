

/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

class SimpleState {

    constructor() {
        this.returnState = "";
        this.currentState = "";
        this.debug = false;
        this.errorCode = 0;
        this.errorMessage = "";
        this.sucessMessage = "";
        this.stateResult = "";

        this.eventListeners = new Map();
    }

    static DONE = 'DONE';
    static NEXT = 'NEXT';
    static ERROR = 'ERROR';

    runStates(returnErrorPage = true) {
        while (this.stateResult = this.dispatchState(this.currentState)) {
            if (this.stateResult === SimpleState.NEXT) {
                continue;
            }
            if (this.stateResult === SimpleState.DONE) {
                break;
            }
            if (this.stateResult === SimpleState.ERROR) {
                if (returnErrorPage) {
                    this.displayErrorMessage(this.errorCode, this.errorMessage);
                }
                break;
            }
        }
    }

    dispatchState(state) {
        return state();
    }

    displayErrorMessage(errorCode, errorMessage) {
        console.log(`Error: ${errorMessage} with code ${errorCode}`)
    }


    /**
     * Switch to a new state and pass the arguments to setCurrentState
     *
     * @param {function} state - The function representing the new state of the state machine.
     * @param {*} [stateResult = null] - The optional state result to be returned.
     * @param {...*} args - The arguments to be passed to the `currentState` function.
     * @returns {Object | *} - The current object or the state result.
     *
     * @example
     * // passing multiple arguments
     * object.switchState(object.StateName, arg1, arg2);
     *
     * @example
     * // passing an object with the arguments
     * object.switchState(object.StateName,{arg1:value1,arg2:value2});
     */
    switchState(state, stateResult = null, ...args) {
        this.setCurrentState(state, ...args);
        if (this.debug) {
            console.log(`State Switched To ${state}`);
        }

        this.triggerEvent('stateSwitched', state, stateResult);
        if (stateResult !== null) {
            return stateResult;
        }
        return this;
    }

    on(eventName, listener) {
        if (!this.eventListeners.has(eventName)) {
            this.eventListeners.set(eventName, []);
        }
        this.eventListeners.get(eventName).push(listener);
    }

    triggerEvent(eventName, ...args) {
        if (this.eventListeners.has(eventName)) {
            const listeners = this.eventListeners.get(eventName);
            listeners.forEach((listener) => {
                listener.apply(null, args);
            });
        }
    }

    getCurrentState() {
        return this.currentState;
    }

    /**
     * Set the current state of the state machine.
     *
     * @param {function} currentState - The function representing the new state of the state machine.
     * @param {...*} [args] - The arguments to be passed to the `currentState` function.
     * @returns {Object} - The current object, allowing for method chaining.
     *
     * @example
     * // passing multiple arguments
     * object.setCurrentState(object.StateName, arg1, arg2);
     *
     * @example
     * // passing an object with the arguments
     * object.setCurrentState(object.StateName,{arg1:value1,arg2:value2});
     */
    setCurrentState(currentState, ...args) {
        if (args.length === 0) {
            this.currentState = currentState.bind(this);
        } else {
            this.currentState = currentState.bind(this, args);
        }
        return this;
    }

    getReturnState() {
        return this.returnState;
    }

    setReturnState(returnState) {
        this.returnState = returnState;
        return this;
    }

    isDebug() {
        return this.debug;
    }

    setDebug(debug) {
        this.debug = debug;
    }

    getErrorCode() {
        return this.errorCode;
    }

    setErrorCode(errorCode) {
        this.errorCode = errorCode;
        return this;
    }

    getErrorMessage() {
        return this.errorMessage;
    }

    setErrorMessage(errorMessage) {
        this.errorMessage = errorMessage;
        return this;
    }

    getStateResult() {
        return this.stateResult;
    }

    setStateResult(stateResult) {
        this.stateResult = stateResult;
    }

    getSuccessMessage() {
        return this.sucessMessage;
    }

    setSuccessMessage(successMessage) {
        this.sucessMessage = successMessage;
    }
}


