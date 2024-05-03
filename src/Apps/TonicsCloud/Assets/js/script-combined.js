var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/Util/Http/XHRApi.ts
var XHRApi = class {
  constructor(headers = {}) {
    this.$callbacks = {};
    this.http = new XMLHttpRequest();
    this.headers = headers;
    this.settings();
  }
  getCallbacks() {
    return this.$callbacks;
  }
  settings() {
    this.getCallbacks().callbacks = {
      onProgress: null
    };
  }
  checkIfCallbackIsSet() {
    if (!this.getCallbacks().callbacks) {
      throw new DOMException("No Callbacks exist");
    }
    return true;
  }
  onProgress($onProgress) {
    if (this.checkIfCallbackIsSet()) {
      this.getCallbacks().callbacks.onProgress = $onProgress;
      return this;
    }
  }
  Get(url, callBack) {
    this.getHttp().open("GET", url, true);
    this.setHeaders();
    this.getHttp().send();
    let self = this;
    this.getHttp().onreadystatechange = function() {
      try {
        if (self.http.readyState === XMLHttpRequest.DONE) {
          if (self.http.status === 200) {
            callBack(null, self.http.response);
          } else {
            callBack(self.http.response);
          }
        }
      } catch (e) {
        callBack("Something Went Wrong: " + e.description);
      }
    };
  }
  Post(url, data, callBack) {
    this.getHttp().open("POST", url, true);
    this.setHeaders();
    this.getHttp().send(data);
    let self = this;
    let onProgress = self.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    this.getHttp().onreadystatechange = function() {
      try {
        self.http.onload = function() {
          callBack(null, self.http.responseText);
        };
      } catch (e) {
        callBack("Something Went Wrong: " + e.description);
      }
    };
  }
  Put(url, data, callBack) {
    this.getHttp().open("PUT", url, true);
    this.setHeaders();
    this.getHttp().send(data);
    let self = this;
    let onProgress = self.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    try {
      this.http.onload = function() {
        if (self.http.status === 200) {
          callBack(null, self.http.response);
        } else {
          callBack(self.http.response);
        }
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
  }
  Delete(url, data = null, callBack) {
    this.http.open("DELETE", url, true);
    this.setHeaders();
    if (data) {
      this.http.send(data);
    } else {
      this.http.send();
    }
    let self = this;
    try {
      this.http.onload = function() {
        if (self.http.status === 200) {
          callBack(null, self.http.response);
        } else {
          callBack(self.http.response);
        }
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
  }
  getHeaders() {
    return this.headers;
  }
  setHeaders() {
    if (this.getHeaders()) {
      for (let key in this.getHeaders()) {
        this.getHttp().setRequestHeader(key, this.getHeaders()[key]);
      }
    }
  }
  getHttp() {
    return this.http;
  }
};
__name(XHRApi, "XHRApi");
if (!window.hasOwnProperty("TonicsScript")) {
  window["TonicsScript"] = {};
}
window["TonicsScript"].XHRApi = (headers = {}) => new XHRApi(headers);
export {
  XHRApi
};


/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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



export class TrackCart extends SimpleState {

    static cartStorageKey = 'Tonics_Cart_Key_Audio_Store';
    shakeCartButtonAnimation = true;
    cartStorageData = new Map();
    licenseData = null;
    cartItemToRemove = null;

    constructor() {
        super();
    }

    getCartStorageKey()
    {
        return TrackCart.cartStorageKey;
    }

    InitialState() {
        let cart = this.getCart();
        cart.set(this.licenseData.slug_id, this.licenseData);
        this.cartStorageData = cart;
        return this.switchState(this.AddCartToLocalStorageState, SimpleState.NEXT);
    }

    AddCartToLocalStorageState() {
        localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(this.cartStorageData)));
        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    UpdateCartLicenseInfo() {
        let cartHeader = document.querySelector('.tonics-cart-items-container');
        if (cartHeader){
            const cart = this.getCart();
            // Remove All Cart Items
            let cartItems = document.querySelectorAll(`.cart-item[data-slug_id]`);
            if (cartItems){
                cartItems.forEach((cartItem) => {
                    cartItem.remove();
                });
            }
            for (let [key, value] of cart.entries()) {
                cartHeader.insertAdjacentHTML('beforeend', this.getLicenseFrag(value));
            }
        }

        return this.switchState(this.UpdateCartBasketNumberState, SimpleState.NEXT);
    }

    UpdateCartBasketNumberState() {
        let cartCounter = document.querySelector('.cb-counter-label');
        if (cartCounter){
            cartCounter.innerHTML = `${this.getCart().size}`;
            if (this.shakeCartButtonAnimation){
                this.shakeCartButton();
            }
        }
        return this.switchState(this.TotalItemsPriceInCartState, SimpleState.NEXT);
    }

    RemoveItemFromCartState() {
        if (this.cartItemToRemove){
            let slug_id = this.cartItemToRemove?.dataset?.slug_id;
            let cart = this.getCart();
            if (cart.has(slug_id)){
                this.cartItemToRemove.remove();
                cart.delete(slug_id);
                localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
            }
        }

        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    TotalItemsPriceInCartState() {
        let tonicsCheckoutPrice = document.querySelector('.tonics-checkout-price');

        if (tonicsCheckoutPrice){
            let currency = 'USD', locale = 'en-US';
            let totalPrice = this.getTotalItemPrice();

            // Format it in USD
            // Create our CURRENCY Formatter, thanks to Intl.NumberFormat.
            // Usage is formatter.format(2500); /* $2,500.00 */
            const formatter = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
            });
            totalPrice = formatter.format(totalPrice);
            tonicsCheckoutPrice.innerHTML = `${totalPrice}`;
        }

        return SimpleState.DONE;
    }

    ReloadCartFromLocalStorageState() {
        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    // That is if a cart is added to the cart menu, we change the cart icon to remove icon
    // this way, a user can remove the cart icon
    UpdateCartIconAdditionToTheCartMenuState(args) {
        if(args.length > 0){
            let trackDownloadContainer = args[0];
            let trackSlugID = trackDownloadContainer.closest('[data-slug_id]')?.dataset.slug_id;
            let licenses = trackDownloadContainer.querySelectorAll('[data-unique_id]');
            let cart = this.getCart();
            if(licenses.length > 0){
                licenses.forEach((license) => {
                    // By Default, we remove the remove icon even if we would later add it when the unique_id matches
                    this.removeIconDeleteButton(license);

                    for (let [key, value] of cart.entries()) {

                        if (trackSlugID !== key){
                            continue;
                        }

                        let licenseUniqueID = license.dataset?.unique_id;
                        let cartStorageUniqueID = value?.unique_id;
                        if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                            let buttonTitle = license.title;
                            let svgElement = license.querySelector('svg');
                            let useElement = license.querySelector('use');

                            if (svgElement && useElement){
                                license.dataset.remove_from_cart = 'true';
                                license.title = 'Remove From Cart'
                                svgElement.dataset.prev_button_title = buttonTitle;
                                svgElement.classList.add('color:red')
                                useElement.setAttribute("xlink:href", "#tonics-remove");
                            }
                            break;
                        }
                    }
                });
            }
            return SimpleState.DONE;
        }

    }

    RemoveItemFromCartWithUniqueID(args) {
        if(args.length > 0){
            let licenseButton = args[0];
            let licenseUniqueID = licenseButton.dataset?.unique_id;
            let trackSlugID = licenseButton.closest('[data-slug_id]')?.dataset.slug_id;
            let cart = this.getCart();

            for (let [key, value] of cart.entries()) {

                if (trackSlugID !== key){
                    continue;
                }

                let cartStorageUniqueID = value?.unique_id;
                if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                    this.removeIconDeleteButton(licenseButton);
                    cart.delete(key);
                    localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
                    break;
                }
            }
        }

        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    removeIconDeleteButton(licenseButton){
        let svgElement = licenseButton.querySelector('svg');
        let useElement = licenseButton.querySelector('use')

        if (!licenseButton.dataset.hasOwnProperty('indie_license_type_is_free') && (svgElement && useElement)){
            licenseButton.removeAttribute("data-remove_from_cart");
            licenseButton.title = svgElement?.dataset?.prev_button_title ?? licenseButton.title;
            svgElement.removeAttribute("data-prev_button_title");
            svgElement.classList.remove('color:red')
            useElement.setAttribute("xlink:href", "#tonics-cart");
        }
    }

    getCart() {
        if (localStorage.getItem(TrackCart.cartStorageKey) !== null) {
            let storedMap = localStorage.getItem(TrackCart.cartStorageKey);
            this.cartStorageData = new Map(JSON.parse(storedMap));
        }
        return this.cartStorageData;
    }

    getCheckOutEmail() {
        return document.querySelector('.checkout-email-tonics');
    }

    addCheckoutEmailInvalid() {
        let emailInput = document.querySelector('.checkout-email-tonics');
        let checkoutEmailContainer = document.querySelector('.checkout-email-error-container');
        let checkoutEmailErrorMessageSpanEl = document.querySelector('.checkout-email-error');

        if (checkoutEmailContainer){
            checkoutEmailContainer.classList.remove('d:none');
        }

        if (emailInput){
            emailInput.setAttribute('aria-invalid', 'true');
            emailInput.setAttribute('aria-describedby', checkoutEmailErrorMessageSpanEl.id);
        }

        if (checkoutEmailErrorMessageSpanEl){
            checkoutEmailErrorMessageSpanEl.setAttribute('aria-live', 'assertive');
        }
    }

    removeCheckoutEmailInvalid() {
        let emailInput = document.querySelector('.checkout-email-tonics');
        let checkoutEmailContainer = document.querySelector('.checkout-email-error-container');
        let checkoutEmailErrorMessageSpanEl = document.querySelector('.checkout-email-error');

        if (checkoutEmailContainer){
            checkoutEmailContainer.classList.add('d:none');
        }

        if (emailInput){
            emailInput.setAttribute('aria-invalid', 'false');
            emailInput.removeAttribute('aria-describedby');
        }

        if (checkoutEmailErrorMessageSpanEl){
            checkoutEmailErrorMessageSpanEl.removeAttribute('aria-live');
        }
    }

    /**
     * This method calculates the total price of all items in the cart, taking into account the quantity of each item.
     * @returns {unknown}
     */
    getTotalItemPrice() {
        // Convert the Map returned by `this.getCart()` into an array using `Array.from()`, and then use the `Array.reduce()` method to calculate the total price.
        return Array.from(this.getCart().values())
            // if quantity is not available, we default to 1
            .reduce((total, { price, quantity = 1 }) => {
                // For each item in the cart, check if it has a valid `price` property, and if so, calculate the item price by multiplying the price by the quantity.
                if (price) {
                    total += parseFloat(price) * parseInt(quantity);
                } else {
                    // If the item is missing a `price` property, log an error message to the console with details of the invalid item.
                    console.error(`Invalid item in cart: ${JSON.stringify({ price, quantity })}`);
                }

                return total; // Return the running total of item prices.
            }, 0); // The initial value of the total is set to 0.
    }

    getLicenseFrag(data) {
        let currency = '$';
        return `            
            <div data-slug_id="${data.slug_id}" class="cart-item d:flex flex-d:row flex-wrap:wrap padding:2rem-1rem align-items:center flex-gap">
                <img data-audioplayer_globalart src="${data.track_image}" class="image:avatar" 
                alt="${data.track_title}">
                <div class="cart-detail">
                    <a data-tonics_navigate data-url_page="${data.url_page}" href="${data.url_page}"><span class="text cart-title color:black">${data.track_title}</span></a> 
                    <span class="text cart-license-price">${data.name}
                <span> → (${currency}${data.price})</span>
            </span>
                    <button data-slug_id="${data.slug_id}" class="tonics-remove-cart-item background:transparent border:none color:black bg:white-one border-width:default border:black padding:small cursor:pointer button:box-shadow-variant-1">
                        <span class="text text:no-wrap">Remove</span>
                    </button>
                </div>
            </div>`;
    }

    shakeCartButton(){
        let cartButton = document.querySelector('.cart-button');
        if (cartButton){
            cartButton.classList.add("jello-diagonal-1"); // Add Animation to cart button
            setTimeout(function () { // Remove Animation After 1 sec
                cartButton.classList.remove("jello-diagonal-1");
            }, 1000);
        }
    }
}

//----------------------
//--- PAYMENT EVENTS
//---------------------

class TonicsPaymentEventAbstract {

    get_request_flow_address = "/modules/track/payment/get_request_flow";
    post_request_flow_address = "/modules/track/payment/post_request_flow";

    constructor(event) {
        this.updateSettings();
        if (this.isEnabled()){
            this.bootPayment(event);
        }
    }

    updateSettings() {

    }

    getPaymentName() {
    }

    getPaymentButton() {

    }

    bootPayment() {

    }

    isEnabled() {
        let paymentName = this.getPaymentName().toLowerCase();
        let query = document.querySelector(`[data-trackpaymenthandler="${paymentName}"]`);
        return !!query;
    }
}

class OnPaymentGatewayCollatorEvent {

    checkout_button_div_el = document.querySelector('.checkout-payment-gateways-buttons');

    addPaymentButton(string) {
        if (this.checkout_button_div_el) {
            let loadingAnimation = this.checkout_button_div_el.querySelector('.loading-button-payment-gateway');
            if (loadingAnimation && !loadingAnimation.classList.contains('d:none')) {
                loadingAnimation.classList.add('d:none');
            }
            this.checkout_button_div_el.insertAdjacentHTML('beforeend', string)
        }
    }

    generateInvoiceID(PaymentHandlerName, GetRequestFlowAddress, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "GenerateInvoiceID"
        }).Get(GetRequestFlowAddress, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            }

            if (err) {
                onError()
            }
        });
    }

    getClientCredentials(PaymentHandlerName, GetRequestFlowAddress, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "ClientPaymentCredentials"
        }).Get(GetRequestFlowAddress, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            }

            if (err) {
                onError()
            }
        });
    }

    getCSRFFromInput(csrfNames) {

        let csrf = null;
        csrfNames.forEach(((value, index) => {
            let inputCSRF = document.querySelector(`input[name=${value}]`)?.value;
            if (!inputCSRF){
                inputCSRF = document.querySelector(`meta[name=${value}]`)?.content;
            }
            if (!csrf && inputCSRF){
                csrf = inputCSRF;
            }
        }))
        return csrf;
    }

    sendBody(PaymentHandlerName, PostRequestFlowAddress, BodyData, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "CapturedPaymentDetails",
            'Tonics-CSRF-Token': `${this.getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
        }).Post(PostRequestFlowAddress, JSON.stringify(BodyData), function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            } else {
                if (onError) {
                    onError();
                }
            }
            if (err) {
                onError()
            }
        });
    }

    /**
     * Load External or Internal Script Asynchronously
     * @param $scriptPath
     * e.g /js/script/tonics.js
     * @param $uniqueIdentifier
     * e.g. tonics, this is useful for preventing the script from loading twice
     */
    loadScriptDynamically($scriptPath, $uniqueIdentifier) {
        return new Promise((resolve, reject) => {
            let scriptCheck = document.querySelector(`[data-script_id="${$uniqueIdentifier}"]`);
            // if script has previously been loaded, resolve
            if (scriptCheck) {
                resolve();
                // else...load script
            } else {
                const script = document.createElement('script');
                script.dataset.script_id = $uniqueIdentifier;
                document.body.appendChild(script);
                script.onload = resolve;
                script.onerror = reject;
                script.async = true;
                script.src = $scriptPath;
            }
        });
    }
}

//----------------------
//--- PAYMENT HANDLERS
//---------------------

class DefaultTonicsPayStackWaveGateway extends TonicsPaymentEventAbstract{
    invoice_id = null;
    client_id = null;
    script_path = 'https://js.paystack.co/v2/inline.js';

    constructor(event) {
        super(event);
    }

    getPaymentName() {
        return "AudioTonicsPayStackHandler";
    }

    getPaymentButton() {
        let name = this.getPaymentName();
        return `
               <div id="${name}">
                    <button type="button" class="d:flex align-items:center text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cursor:pointer button:box-shadow-variant-1" style="gap:0.3em;">
                        <span class="paypal-button-text true">Pay with </span>
<img src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9Ii0zOTMiIHZpZXdCb3g9Ii0xMzEuMiAyMjIgNjAwLjIgMTA2LjkiIHdpZHRoPSIyNTAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im0tNDUuOCAyMzIuMmgtODAuNGMtMi43IDAtNSAyLjMtNSA1LjF2OS4xYzAgMi44IDIuMyA1LjEgNSA1LjFoODAuNGMyLjggMCA1LTIuMyA1LjEtNS4xdi05YzAtMi45LTIuMy01LjItNS4xLTUuMnptMCA1MC41aC04MC40Yy0xLjMgMC0yLjYuNS0zLjUgMS41LTEgMS0xLjUgMi4yLTEuNSAzLjZ2OS4xYzAgMi44IDIuMyA1LjEgNSA1LjFoODAuNGMyLjggMCA1LTIuMiA1LjEtNS4xdi05LjFjLS4xLTIuOS0yLjMtNS4xLTUuMS01LjF6bS0zNS4xIDI1LjJoLTQ1LjNjLTEuMyAwLTIuNi41LTMuNSAxLjVzLTEuNSAyLjItMS41IDMuNnY5LjFjMCAyLjggMi4zIDUuMSA1IDUuMWg0NS4yYzIuOCAwIDUtMi4zIDUtNXYtOS4xYy4xLTMtMi4xLTUuMy00LjktNS4yem00MC4yLTUwLjVoLTg1LjVjLTEuMyAwLTIuNi41LTMuNSAxLjVzLTEuNSAyLjItMS41IDMuNnY5LjFjMCAyLjggMi4zIDUuMSA1IDUuMWg4NS40YzIuOCAwIDUtMi4zIDUtNS4xdi05LjFjLjEtMi44LTIuMi01LTQuOS01LjF6bTAgMCIgZmlsbD0iIzAwYzNmNyIvPjxwYXRoIGQ9Im01Mi44IDI1Mi42Yy0yLjUtMi42LTUuNC00LjYtOC43LTZzLTYuOC0yLjEtMTAuNC0yLjFjLTMuNS0uMS02LjkuNy0xMC4xIDIuMi0yLjEgMS00IDIuNC01LjYgNC4xdi0xLjZjMC0uOC0uMy0xLjYtLjgtMi4ycy0xLjMtMS0yLjItMWgtMTEuMWMtLjggMC0xLjYuMy0yLjEgMS0uNi42LS45IDEuNC0uOCAyLjJ2NzQuOGMwIC44LjMgMS42LjggMi4yLjYuNiAxLjMuOSAyLjEuOWgxMS40Yy44IDAgMS41LS4zIDIuMS0uOS42LS41IDEtMS4zLjktMi4ydi0yNS42YzEuNiAxLjggMy43IDMuMSA2IDMuOSAzIDEuMSA2LjEgMS43IDkuMyAxLjcgMy42IDAgNy4yLS43IDEwLjUtMi4xczYuMy0zLjQgOC44LTZjMi42LTIuNyA0LjYtNS45IDYtOS40IDEuNi0zLjkgMi4zLTguMSAyLjItMTIuMy4xLTQuMi0uNy04LjQtMi4yLTEyLjQtMS41LTMuMy0zLjUtNi41LTYuMS05LjJ6bS0xMC4yIDI3LjFjLS42IDEuNi0xLjUgMy0yLjcgNC4zLTIuMyAyLjUtNS42IDMuOS05IDMuOS0xLjcgMC0zLjQtLjMtNS0xLjEtMS41LS43LTIuOS0xLjYtNC4xLTIuOHMtMi4xLTIuNy0yLjctNC4zYy0xLjMtMy40LTEuMy03LjEgMC0xMC41LjYtMS42IDEuNi0zIDIuNy00LjIgMS4yLTEuMiAyLjYtMi4yIDQuMS0yLjkgMS42LS43IDMuMy0xLjEgNS0xLjEgMS44IDAgMy40LjMgNS4xIDEuMSAxLjUuNyAyLjkgMS42IDQgMi44IDEuMiAxLjIgMiAyLjYgMi43IDQuMiAxLjIgMy41IDEuMSA3LjItLjEgMTAuNnptNzkuNi0zMy42aC0xMS4zYy0uOCAwLTEuNi4zLTIuMS45LS42LjYtLjkgMS40LS45IDIuM3YxLjRjLTEuNC0xLjctMy4yLTMtNS4xLTMuOS0zLjEtMS41LTYuNS0yLjItOS45LTIuMi03LjMgMC0xNC4yIDIuOS0xOS40IDgtMi43IDIuNy00LjggNS45LTYuMiA5LjQtMS42IDMuOS0yLjQgOC4xLTIuMyAxMi40LS4xIDQuMi43IDguNCAyLjMgMTIuNCAxLjUgMy41IDMuNSA2LjcgNi4yIDkuNCA1LjEgNS4yIDEyLjEgOC4xIDE5LjMgOC4xIDMuNC4xIDYuOC0uNyA5LjktMi4yIDEuOS0xIDMuOC0yLjMgNS4yLTMuOXYxLjVjMCAuOC4zIDEuNi45IDIuMi42LjUgMS4zLjkgMi4xLjloMTEuM2MuOCAwIDEuNi0uMyAyLjEtLjkuNi0uNi45LTEuNC45LTIuMnYtNTAuM2MwLS44LS4zLTEuNi0uOC0yLjItLjYtLjctMS40LTEuMS0yLjItMS4xem0tMTUuMyAzMy42Yy0uNiAxLjYtMS41IDMtMi43IDQuMy0xLjIgMS4yLTIuNSAyLjItNCAyLjktMy4yIDEuNS02LjkgMS41LTEwLjEgMC0xLjUtLjctMi45LTEuNy00LjEtMi45cy0yLjEtMi43LTIuNy00LjNjLTEuMi0zLjQtMS4yLTcuMSAwLTEwLjUuNi0xLjYgMS41LTIuOSAyLjctNC4yIDEuMi0xLjIgMi41LTIuMiA0LjEtMi45IDMuMi0xLjUgNi45LTEuNSAxMCAwIDEuNS43IDIuOSAxLjYgNCAyLjhzMiAyLjYgMi43IDQuMmMxLjQgMy41IDEuNCA3LjIuMSAxMC42em0xMjcuOS02LjhjLTEuNi0xLjQtMy41LTIuNi01LjUtMy40LTIuMS0uOS00LjQtMS41LTYuNi0ybC04LjYtMS43Yy0yLjItLjQtMy44LTEtNC42LTEuNy0uNy0uNS0xLjItMS4zLTEuMi0yLjJzLjUtMS43IDEuNi0yLjRjMS41LS44IDMuMS0xLjIgNC44LTEuMSAyLjIgMCA0LjQuNSA2LjQgMS4zIDIgLjkgMy45IDEuOCA1LjcgMyAyLjUgMS42IDQuNyAxLjMgNi4yLS41bDQuMS00LjdjLjgtLjggMS4yLTEuOCAxLjMtMi45LS4xLTEuMi0uNy0yLjItMS42LTMtMS43LTEuNS00LjUtMy4xLTguMi00LjdzLTguNC0yLjQtMTMuOS0yLjRjLTMuNC0uMS02LjcuNC05LjkgMS40LTIuNy45LTUuMyAyLjItNy42IDMuOS0yLjEgMS42LTMuNyAzLjYtNC45IDYtMS4xIDIuMy0xLjcgNC44LTEuNyA3LjMgMCA0LjcgMS40IDguNSA0LjIgMTEuM3M2LjUgNC43IDExLjEgNS42bDkgMmMxLjkuMyAzLjkuOSA1LjcgMS44IDEgLjQgMS42IDEuNCAxLjYgMi41IDAgMS0uNSAxLjktMS42IDIuN3MtMi45IDEuMy01LjMgMS4zLTQuOS0uNS03LjEtMS42Yy0yLjEtMS00LTIuMy01LjgtMy44LS44LS42LTEuNi0xLjEtMi42LTEuNS0xLS4zLTIuMyAwLTMuNiAxLjFsLTQuOSAzLjdjLTEuNCAxLTIuMSAyLjctMS43IDQuMy4zIDEuNyAxLjYgMy4zIDQuMSA1LjIgNi4yIDQuMiAxMy42IDYuNCAyMS4xIDYuMiAzLjUgMCA3LS40IDEwLjMtMS40IDIuOS0uOSA1LjYtMi4yIDgtNCAyLjItMS42IDQtMy43IDUuMi02LjIgMS4yLTIuNCAxLjgtNSAxLjgtNy43LjEtMi40LS40LTQuOC0xLjQtNy0xLTEuNi0yLjMtMy4zLTMuOS00Ljd6bTQ5LjQgMTMuN2MtLjUtLjktMS40LTEuNS0yLjUtMS43LTEgMC0yLjEuMy0yLjkuOS0xLjQuOS0zIDEuNC00LjYgMS41LS41IDAtMS4xLS4xLTEuNi0uMi0uNi0uMS0xLjEtLjQtMS41LS44LS41LS41LS45LTEuMS0xLjItMS43LS40LTEtLjYtMi0uNS0zdi0yMC41aDE0LjZjLjkgMCAxLjctLjQgMi4zLTFzMS0xLjMgMS0yLjJ2LTguN2MwLS45LS4zLTEuNy0xLTIuMi0uNi0uNi0xLjQtLjktMi4yLS45aC0xNC43di0xNGMwLS44LS4zLTEuNy0uOS0yLjJzLTEuMy0uOC0yLjEtLjloLTExLjRjLS44IDAtMS42LjMtMi4yLjlzLTEgMS40LTEgMi4ydjE0aC02LjVjLS44IDAtMS42LjMtMi4yIDEtLjUuNi0uOCAxLjQtLjggMi4ydjguN2MwIC44LjMgMS42LjggMi4yLjUuNyAxLjMgMSAyLjIgMWg2LjV2MjQuNGMtLjEgMi45LjUgNS44IDEuNyA4LjQgMS4xIDIuMiAyLjUgNC4xIDQuNCA1LjcgMS44IDEuNSAzLjkgMi42IDYuMiAzLjIgMi4zLjcgNC43IDEuMSA3LjEgMS4xIDMuMSAwIDYuMy0uNSA5LjMtMS41IDIuOC0uOSA1LjMtMi41IDcuMy00LjYgMS4zLTEuMyAxLjQtMy40LjQtNC45em02MS44LTQwLjVoLTExLjNjLS44IDAtMS41LjMtMi4xLjlzLS45IDEuNC0uOSAyLjN2MS40Yy0xLjQtMS43LTMuMS0zLTUuMS0zLjktMy4xLTEuNS02LjUtMi4yLTkuOS0yLjItNy4zIDAtMTQuMiAyLjktMTkuNCA4LTIuNyAyLjctNC44IDUuOS02LjIgOS40LTEuNiAzLjktMi40IDguMS0yLjMgMTIuMy0uMSA0LjIuNyA4LjQgMi4zIDEyLjQgMS40IDMuNSAzLjYgNi43IDYuMiA5LjQgNS4xIDUuMiAxMiA4LjEgMTkuMyA4LjEgMy40LjEgNi44LS43IDkuOS0yLjEgMi0xIDMuOC0yLjMgNS4yLTMuOXYxLjVjMCAuOC4zIDEuNi45IDIuMS42LjYgMS4zLjkgMi4xLjloMTEuM2MxLjcgMCAzLTEuMyAzLTN2LTUwLjNjMC0uOC0uMy0xLjYtLjgtMi4yLS41LS43LTEuMy0xLjEtMi4yLTEuMXptLTE1LjIgMzMuNmMtLjYgMS42LTEuNSAzLTIuNyA0LjMtMS4yIDEuMi0yLjUgMi4yLTQgMi45LTEuNi43LTMuMyAxLjEtNS4xIDEuMXMtMy40LS40LTUtMS4xYy0xLjUtLjctMi45LTEuNy00LjEtMi45cy0yLjEtMi43LTIuNi00LjNjLTEuMi0zLjQtMS4yLTcuMSAwLTEwLjUuNi0xLjYgMS41LTMgMi42LTQuMiAxLjItMS4yIDIuNi0yLjIgNC4xLTIuOSAxLjYtLjcgMy4zLTEuMSA1LTEuMXMzLjQuMyA1LjEgMS4xYzEuNS43IDIuOCAxLjYgNCAyLjhzMi4xIDIuNiAyLjcgNC4yYzEuMyAzLjQgMS4zIDcuMiAwIDEwLjZ6bTc3LjIgNi4xLTYuNS01Yy0xLjItMS0yLjQtMS4zLTMuNC0uOS0uOS40LTEuNyAxLTIuNCAxLjctMS40IDEuNy0zLjEgMy4yLTQuOSA0LjUtMiAxLjEtNC4xIDEuNy02LjMgMS41LTIuNiAwLTUtLjctNy4xLTIuMnMtMy43LTMuNS00LjUtNmMtLjYtMS43LS45LTMuNC0uOS01LjEgMC0xLjguMy0zLjUuOS01LjMuNi0xLjYgMS40LTMgMi42LTQuMnMyLjUtMi4yIDQtMi44YzEuNi0uNyAzLjMtMS4xIDUuMS0xLjEgMi4yLS4xIDQuNC41IDYuMyAxLjYgMS45IDEuMiAzLjUgMi43IDQuOSA0LjUuNi43IDEuNCAxLjMgMi4zIDEuNyAxIC40IDIuMi4xIDMuNC0uOWw2LjUtNC45Yy44LS41IDEuNC0xLjMgMS43LTIuMi40LTEgLjMtMi4xLS4zLTMtMi41LTMuOS01LjktNy4xLTEwLTkuNC00LjMtMi40LTkuNC0zLjctMTUuMS0zLjctNCAwLTggLjgtMTEuOCAyLjMtMy42IDEuNS02LjggMy42LTkuNSA2LjNzLTQuOSA1LjktNi40IDkuNWMtMy4xIDcuNS0zLjEgMTUuOSAwIDIzLjQgMS41IDMuNSAzLjYgNi44IDYuNCA5LjQgNS43IDUuNiAxMy4zIDguNiAyMS4zIDguNiA1LjcgMCAxMC44LTEuMyAxNS4xLTMuNyA0LjEtMi4zIDcuNi01LjUgMTAuMS05LjUuNS0uOS42LTIgLjMtMi45LS40LS44LTEtMS42LTEuOC0yLjJ6bTYwLjIgMTEuNy0xNy45LTI2LjIgMTUuMy0yMC4yYy43LS45IDEtMi4yLjYtMy4zLS4zLS44LTEtMS42LTIuOS0xLjZoLTEyLjFjLS43IDAtMS40LjItMiAuNS0uOC40LTEuNCAxLTEuOCAxLjdsLTEyLjIgMTcuMWgtMi45di00MC40YzAtLjgtLjMtMS42LS45LTIuMnMtMS4zLS45LTIuMS0uOWgtMTEuM2MtLjggMC0xLjYuMy0yLjIuOXMtLjkgMS4zLS45IDIuMnY3NC41YzAgLjkuMyAxLjYuOSAyLjJzMS40LjkgMi4yLjloMTEuM2MuOCAwIDEuNi0uMyAyLjEtLjkuNi0uNi45LTEuNC45LTIuMnYtMTkuN2gzLjJsMTMuMyAyMC40Yy44IDEuNSAyLjMgMi40IDMuOSAyLjRoMTIuN2MxLjkgMCAyLjctLjkgMy4xLTEuNy41LTEuMi40LTIuNS0uMy0zLjV6bS0yODEuOC01MS40aC0xMi43Yy0xIDAtMS45LjMtMi42IDEtLjYuNi0xIDEuMy0xLjIgMi4xbC05LjQgMzQuOGgtMi4zbC0xMC0zNC44Yy0uMi0uNy0uNS0xLjQtMS0yLjEtLjYtLjctMS40LTEuMS0yLjMtMS4xaC0xMi45Yy0xLjcgMC0yLjcuNS0zLjIgMS43LS4zIDEtLjMgMi4xIDAgMy4xbDE2IDQ5Yy4zLjcuNiAxLjUgMS4yIDIgLjYuNiAxLjUuOSAyLjQuOWg2LjhsLS42IDEuNi0xLjUgNC41Yy0uNSAxLjQtMS4zIDIuNi0yLjUgMy41LTEuMS44LTIuNCAxLjMtMy44IDEuMi0xLjIgMC0yLjMtLjMtMy40LS43LTEuMS0uNS0yLjEtMS4xLTMtMS44LS44LS42LTEuOC0uOS0yLjktLjloLS4xYy0xLjIuMS0yLjMuNy0yLjkgMS44bC00IDUuOWMtMS42IDIuNi0uNyA0LjIuMyA1LjEgMi4yIDIgNC43IDMuNSA3LjUgNC40IDMuMSAxLjEgNi4zIDEuNiA5LjUgMS42IDUuOCAwIDEwLjYtMS42IDE0LjMtNC43IDMuOC0zLjQgNi43LTcuOCA4LjEtMTIuOGwxOC42LTYwLjZjLjQtMS4xLjUtMi4yLjEtMy4yLS4xLS43LS44LTEuNS0yLjUtMS41em0wIDAiIGZpbGw9IiMwMTFiMzMiLz48L3N2Zz4=" alt="FlutterWave Logo" role="presentation" style="width:85px;">
</button>
                </div>
        `;
    }

    bootPayment(event = null) {
        let self = this;
        if (event) {
            event.getClientCredentials(self.getPaymentName(), self.get_request_flow_address, (data) => {
                self.client_id = data?.data;
                event.loadScriptDynamically(self.script_path, 'paystack')
                    .then(() => {
                        event.addPaymentButton(self.getPaymentButton());
                        self.initPayStackButton(event);
                    });
            })
        }
    }

    initPayStackButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const cart = new TrackCart();
                    const currency = 'USD';
                    const totalPrice = cart.getTotalItemPrice();
                    const payeeEmail = cart.getCheckOutEmail();

                    if (payeeEmail && payeeEmail.checkValidity()) {
                        cart.removeCheckoutEmailInvalid();
                        event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                            self.invoice_id = data?.data;
                            if (self.invoice_id) {
                                FlutterwaveCheckout({
                                    public_key: self.client_id,
                                    tx_ref: self.invoice_id,
                                    amount: totalPrice,
                                    currency: currency,
                                    payment_options: "card",
                                    callback: function(orderData) {
                                        // Send AJAX verification request to backend
                                        if (orderData.status === 'successful') {
                                            const cart = new TrackCart();
                                            const checkOutEmail = cart.getCheckOutEmail();
                                            const body = {
                                                invoice_id: self.invoice_id,
                                                checkout_email: checkOutEmail.value,
                                                orderData: orderData,
                                                cartItems: Array.from(cart.getCart())
                                            };

                                            event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                                body,
                                                (data) => {
                                                    // Show a success message within this page, e.g.
                                                    const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                    element.innerHTML = '';
                                                    element.innerHTML = data?.message;
                                                    const cart = new TrackCart();
                                                    localStorage.removeItem(cart.getCartStorageKey())
                                                    // Reload TonicsCart Data From LocalStorage
                                                    cart.setCurrentState(cart.ReloadCartFromLocalStorageState);
                                                    cart.runStates();
                                                    // Or go to another URL:  actions.redirect('thank_you.html');
                                                },
                                                (error) => {

                                                });
                                        }
                                    },
                                    onclose: function(incomplete) {
                                        console.log("Closed", incomplete)
                                    },
                                    meta: self.getItems(cart.getCart(), currency),
                                    customer: {
                                        email: payeeEmail.value
                                    },
                                    customizations: {
                                        title: "Audio Store",
                                    },
                                });
                            } else {
                                reject('Invalid Invoice ID');
                            }

                        }, () => {
                            reject('Something Went Wrong Processing Payment');
                        });
                    } else {
                        cart.addCheckoutEmailInvalid();
                        reject('Invalid Email Address');
                    }
                });
            });
        }
    }

    getItems(cart, currency = 'USD') {
        let info = '';
        for (let [key, value] of cart.entries()) {
            info += '[ Title:' + value.track_title + ' -> Price: ' + value?.price + ' ]'
        }
        return {
            item_info: info
        }
    }

}

class DefaultTonicsFlutterWaveGateway extends TonicsPaymentEventAbstract{
    invoice_id = null;
    client_id = null;
    script_path = 'https://checkout.flutterwave.com/v3.js';

    constructor(event) {
        super(event);
    }

    getPaymentName() {
        return "AudioTonicsFlutterWaveHandler";
    }

    getPaymentButton() {
        let name = this.getPaymentName();
        return `
               <div id="${name}">
                    <button type="button" class="d:flex align-items:center text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cursor:pointer button:box-shadow-variant-1" style="gap:0.3em;"><span class="paypal-button-text true">Pay with </span>
<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8IS0tIENyZWF0b3I6IENvcmVsRFJBVyAyMDIxICg2NC1CaXQpIC0tPg0KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSIxMC41NzhpbiIgaGVpZ2h0PSIxLjY2OTk4aW4iIHZlcnNpb249IjEuMSIgc3R5bGU9InNoYXBlLXJlbmRlcmluZzpnZW9tZXRyaWNQcmVjaXNpb247IHRleHQtcmVuZGVyaW5nOmdlb21ldHJpY1ByZWNpc2lvbjsgaW1hZ2UtcmVuZGVyaW5nOm9wdGltaXplUXVhbGl0eTsgZmlsbC1ydWxlOmV2ZW5vZGQ7IGNsaXAtcnVsZTpldmVub2RkIg0Kdmlld0JveD0iMCAwIDEwNTc4LjA0IDE2NjkuOTgiDQogeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiDQogeG1sbnM6eG9kbT0iaHR0cDovL3d3dy5jb3JlbC5jb20vY29yZWxkcmF3L29kbS8yMDAzIj4NCiA8ZGVmcz4NCiAgPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCiAgIDwhW0NEQVRBWw0KICAgIC5maWwxIHtmaWxsOiMwMDlBNDY7ZmlsbC1ydWxlOm5vbnplcm99DQogICAgLmZpbDAge2ZpbGw6IzJBMzM2MjtmaWxsLXJ1bGU6bm9uemVyb30NCiAgICAuZmlsMyB7ZmlsbDojRjVBRkNCO2ZpbGwtcnVsZTpub256ZXJvfQ0KICAgIC5maWwyIHtmaWxsOiNGRjU4MDU7ZmlsbC1ydWxlOm5vbnplcm99DQogICAgLmZpbDQge2ZpbGw6I0ZGOUIwMDtmaWxsLXJ1bGU6bm9uemVyb30NCiAgIF1dPg0KICA8L3N0eWxlPg0KIDwvZGVmcz4NCiA8ZyBpZD0iTGF5ZXJfeDAwMjBfMSI+DQogIDxtZXRhZGF0YSBpZD0iQ29yZWxDb3JwSURfMENvcmVsLUxheWVyIi8+DQogIDxnIGlkPSJfMjQ2NDgyODA1OTYwMCI+DQogICA8cG9seWdvbiBjbGFzcz0iZmlsMCIgcG9pbnRzPSIzMjM3LjM5LDM5MC4yNCAzNDIzLjI4LDM5MC4yNCAzNDIzLjI4LDEzNDYuNzUgMzIzNy4zOSwxMzQ2Ljc1ICIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDAiIGQ9Ik00MTQyLjc1IDEwMDcuMzdjMCwxMzIuMDQgLTg0LjkzLDE5MS4zNSAtMTk4LjAzLDE5MS4zNSAtMTEzLjA1LDAgLTE4OC42NiwtNTkuMzEgLTE4OC42NiwtMTg0LjYzbDAgLTM0MC44OSAtMTg1Ljg5IDAgMCAzODQuMDVjMCwxOTEuMTggMTE5LjksMzAzLjAyIDMxOS4zMiwzMDMuMDIgMTI1LjMsMCAxOTUuMzIsLTQ2LjE2IDI0Mi4zMywtOTguMzNsMTAuODEgMCAxNi4xNyA4NC45MyAxNzAuOTggMCAwIC02NzMuNjcgLTE4Ny4wMyAwIDAgMzM0LjE3eiIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDAiIGQ9Ik01OTY2LjMyIDEyMTMuNDVjLTEzNi4wNywwIC0yMTIuODEsLTYxLjk2IC0yMjQuODksLTE1Mi4xN2w1OTAuMDIgMGMyLjcsLTE4Ljc1IDQuMDIsLTM3LjY5IDQuMDIsLTU2LjY4IC0xLjI2LC0yNDIuMzMgLTE4NC42MywtMzQ0LjkyIC0zODAuOCwtMzQ0LjkyIC0yMjcuNjYsMCAtMzk4LjgzLDEzNi4yIC0zOTguODMsMzU1LjggMCwyMDguNzIgMTY0LjQ0LDM0NC44IDQxMC45NywzNDQuOCAyMDYuMjIsMCAzNDMuNDgsLTkxLjUzIDM3MS45LC0yMzEuNzRsLTE4My43MiAwYy0yMi45NSw1NS4yOCAtODguOTQsODQuOTIgLTE4OC42Niw4NC45MnptLTExLjU0IC00MDYuODJjMTE4LjY0LDAgMTg3LjI4LDUyLjQ3IDE5OC4wOSwxMjYuOTNsLTQwNy40MiAwYzE3LjQyLC03MS4zNCA4Ni4xNywtMTI2LjkzIDIwOC43MiwtMTI2LjkzbDAuNiAweiIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDAiIGQ9Ik02NjUxLjMgNzY5LjkxbC0xMS41NCAwIC0xNy41NiAtOTcuMDcgLTE2OC45NCAwIDAgNjc0LjA0IDE4NS44OSAwIDAgLTI4OS42MmMwLC0xMzAuNzggNzUuMzYsLTIwMy40NSAyMTUuNTcsLTIwMy40NSAyNS41LC0wLjM1IDUwLjkxLDEuODYgNzUuOTEsNi42OGwwIC0xODcuMjggLTI1Ljk3IDBjLTEyMy45MiwwIC0yMDAuODUsMjUuNiAtMjUzLjM4LDk2Ljd6Ii8+DQogICA8cG9seWdvbiBjbGFzcz0iZmlsMCIgcG9pbnRzPSI3ODQzLjM5LDExMzYuNjQgNzgzMi43MSwxMTM2LjY0IDc2NzMuNjcsNzE0Ljk4IDc0ODIuMzcsNzE0Ljk4IDczMjYuMTEsMTEzNS4zOCA3MzE0LjAzLDExMzUuMzggNzE2NS44Miw2NzMuMiA2OTgxLjE5LDY3My4yIDcyMDguODYsMTM0Ni44NyA3NDExLjA0LDEzNDYuODcgNzU3Mi43LDkxNy4wMiA3NTgzLjQ3LDkxNy4wMiA3NzQyLjQzLDEzNDYuODcgNzk0NS44NiwxMzQ2Ljg3IDgxNzMuNTksNjczLjIgNzk4OC45Niw2NzMuMiAiLz4NCiAgIDxwYXRoIGNsYXNzPSJmaWwwIiBkPSJNODkxMy40OSAxMTUwLjdsMCAtMjI1LjQ5YzAsLTE4My4yNSAtMTU1LC0yNjUuNDEgLTM0Ny42OSwtMjY1LjQxIC0yMDQuNjksMCAtMzMyLjcyLDk3LjA2IC0zNTAuMjYsMjQyLjMzbDE4NS45NiAwYzEzLjQ2LC02My4zNCA2Ny4zNiwtOTguMzMgMTY0LjMsLTk4LjMzIDk2Ljg5LDAgMTYxLjU2LDM2LjM2IDE2MS41NiwxMTAuNDdsMCAxNy4xOSAtMzAzLjE1IDIzLjA3Yy0xNDAuMDksMTAuODIgLTI0Mi4zMyw3Mi44NCAtMjQyLjMzLDIwNC44MiAwLDEzNi4wNyAxMTcuMjYsMjAwLjY4IDI4OS42MiwyMDAuNjggMTM5LjIsMCAyMjQsLTM4Ljc2IDI3NS45MywtOTcuM2w5LjIgMGMyOS4yLDY1LjgyIDg4LjQxLDgzLjkgMTUyLjc2LDgzLjlsNzguMTMgMCAwIC0xMzcuMjEgLTE3LjU0IDBjLTM5LjA3LDAgLTU2LjUsLTE4LjggLTU2LjUsLTU4Ljcxem0tMTg2LjAxIC03MS40MWMwLDEwNi41IC0xMjYuOTMsMTQxLjQzIC0yMzUuNzgsMTQxLjQzIC03NC4xMSwwIC0xMjIuNTQsLTE4LjgyIC0xMjIuNTQsLTcwLjAyIDAsLTQ2LjE2IDQxLjc3LC02OC42NCAxMDMuODUsLTc0LjFsMjU0LjU5IC0xOC44MiAtMC4xMiAyMS41MXoiLz4NCiAgIDxwb2x5Z29uIGNsYXNzPSJmaWwwIiBwb2ludHM9Ijk2MDAuNzUsNjczLjIgOTM4My44NCwxMTYwLjk4IDkzNzEuNywxMTYwLjk4IDkxNTIuMSw2NzMuMiA4OTUxLjMsNjczLjIgOTI2NS4yLDEzNDYuODcgOTQ4Ny41NywxMzQ2Ljg3IDk4MDAuMDQsNjczLjIgIi8+DQogICA8cGF0aCBjbGFzcz0iZmlsMCIgZD0iTTEwMzk0LjMyIDExMjguNTNjLTIzLjA3LDU1LjI4IC04OC45NCw4NC45MiAtMTg4LjY2LDg0LjkyIC0xMzYuMDYsMCAtMjEyLjgxLC02MS45NiAtMjI0LjksLTE1Mi4xN2w1OTAuMDIgMGMyLjcxLC0xOC43NSA0LjAyLC0zNy42OSA0LjAyLC01Ni42OCAtMS4yNiwtMjQyLjMzIC0xODQuNjMsLTM0NC45MiAtMzgwLjgsLTM0NC45MiAtMjI3LjY1LDAgLTM5OC43MSwxMzYuMiAtMzk4LjcxLDM1NS44IDAsMjA4LjcyIDE2NC4zMiwzNDQuOCA0MTAuOCwzNDQuOCAyMDYuMjYsMCAzNDMuNTQsLTkxLjUzIDM3MS45NSwtMjMxLjc0bC0xODMuNzIgMHptLTIwMC44IC0zMjEuOWMxMTguNjQsMCAxODcuMjgsNTIuNDcgMTk4LjE2LDEyNi45M2wtNDA3LjM2IDBjMTcuNjYsLTcxLjM0IDg2LjE3LC0xMjYuOTMgMjA4Ljc4LC0xMjYuOTNsMC40MiAweiIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDAiIGQ9Ik0yOTM1LjI2IDYyMy41NWMwLC01OS4yNSA0MS43NywtODQuNzkgOTUuNjksLTg0Ljc5IDI1LjA2LDAuMjMgNTAuMDEsMy44NSA3NC4xLDEwLjc1bDMxLjAyIC0xMzQuODFjLTQ1LjAyLC0xNS45MyAtOTIuMzcsLTI0LjA5IC0xNDAuMDgsLTI0LjIyIC0xMzcuNDYsMCAtMjQ3Ljg3LDcyLjg0IC0yNDcuODcsMjIzLjYzbDAgNTkuMDggLTE2MC40MSAwIDAgMTQ3LjYxIDE2MC40MSAwIDAgNTI2LjA1IDE4Ny4xNSAwIDAgLTUyNi4xOCAyMDkuNTcgMCAwIC0xNDcuNDkgLTIwOS41NyAwIDAgLTQ5LjY1eiIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDAiIGQ9Ik00NzQwLjAzIDQ4My4yOGwtMTczLjA5IDAgLTkuNjcgMTg5LjkyIC0xNDYuMzUgMCAwIDE0Ny42MSAxNDIuODcgMCAwIDMzMi4wNWMwLDExMy4xIDU3LjcsMjA3LjcxIDIzMC43OSwyMDcuNzEgNDQuODgsMCA4OS42MSwtNC45NCAxMzMuNDEsLTE0LjcybDAgLTE0MS44NGMtMjUuMjMsNS42NSAtNTEuMDIsOC44MyAtNzYuODcsOS40MyAtOTIuOTEsMCAtMTAwLjk2LC01Mi40NyAtMTAwLjk2LC05Mi45OGwwIC0zMDAuMDIgMTg0LjYzIDAgMCAtMTQ3LjI0IC0xODQuNzYgMCAwIC0xODkuOTJ6Ii8+DQogICA8cGF0aCBjbGFzcz0iZmlsMCIgZD0iTTUzMjMuNzggNDgzLjI4bC0xNzIuNDkgMCAtOS42NiAxODkuOTIgLTE0Ni43MiAwIDAgMTQ3LjYxIDE0Mi44NyAwIDAgMzMyLjA1YzAsMTEzLjEgNTcuNywyMDcuNzEgMjMwLjc5LDIwNy43MSA0NC43OCwtMC4xMiA4OS40MiwtNS4xNyAxMzMuMDYsLTE1LjA5bDAgLTE0MS40N2MtMjUuMjUsNS42NSAtNTAuOTYsOC44MyAtNzYuODcsOS40MyAtOTIuODUsMCAtMTAwLjk4LC01Mi40NyAtMTAwLjk4LC05Mi45OGwwIC0zMDAuMDIgMTg1LjIzIDAgMCAtMTQ3LjI0IC0xODUuMjMgMCAwIC0xODkuOTJ6Ii8+DQogICA8cGF0aCBjbGFzcz0iZmlsMSIgZD0iTS0wIDM2NC43N2MwLC0xMDguMTIgMzEuNjIsLTIwMC40MyA5Ny45NiwtMjY2Ljc5bDExNS4zOSAxMTUuMzljLTEyOC40NCwxMjcuOTcgLTE2LjI0LDUyNS44MyAzNDkuNDMsODkxLjkgMzY1LjY2LDM2Ni4wMSA3NjMuNzcsNDc4LjA0IDg5Mi4xMywzNDkuODVsMTE1LjM5IDExNS4zOWMtMjE2LjQ4LDIxNi40OCAtNzA5LjYxLDYyLjg3IC0xMTIyLjMxLC0zNDkuOTYgLTI4Ni4zMiwtMjg2LjQ0IC00NDcuOTksLTYxMS40IC00NDcuOTksLTg1NS43N3oiLz4NCiAgIDxwYXRoIGNsYXNzPSJmaWwyIiBkPSJNNzI3LjcgMTY2OS45OGMtMTA4LjEzLDAgLTIwMC40NCwtMzEuNjIgLTI2Ni45MSwtOTcuOTZsMTE1LjM5IC0xMTUuNGMxMjguMTksMTI4LjIgNTI2LjA1LDE2LjA1IDg5Mi4wOCwtMzQ5LjkgMzY2LjAxLC0zNjUuOTEgNDc3Ljk4LC03NjMuNzcgMzQ5Ljc4LC04OTEuOTZsMTE1LjM5IC0xMTUuMzljMjE2LjYsMjE2LjQ4IDYyLjg2LDcwOS41NSAtMzUwLjAzLDExMjIuMzEgLTI4Ni4zOCwyODYuNzQgLTYxMS4zNCw0NDguMyAtODU1LjcxLDQ0OC4zeiIvPg0KICAgPHBhdGggY2xhc3M9ImZpbDMiIGQ9Ik0xNjIxLjYzIDEwNTYuNTNjLTcwLjA3LC0yMDEuNyAtMjEzLjExLC00MTcuMjIgLTQwMi45NywtNjA2Ljk1IC00MTIuNzcsLTQxMi45IC05MDUuODQsLTU2Ni41OSAtMTEyMi4zMiwtMzQ5Ljk3IC0xNS4zMiwxNS40NSAtMi4wNSw1My42NyAyOS44MSw4NS40NSAzMS44NSwzMS44NiA3MC4wMiw0NS4wMiA4NS40LDI5LjcgMTI4LjMyLC0xMjguMiA1MjYuMTgsLTE2LjA1IDg5Mi4wNywzNDkuOTYgMTczLjA5LDE3My4wOSAzMDIuMTMsMzY2LjM4IDM2NC4yNyw1NDUuMjQgNTQuNTEsMTU2Ljg2IDQ5LjE2LDI4My4zMiAtMTQuMywzNDYuNzggLTE1LjQ0LDE1LjMyIC0yLjA0LDUzLjY3IDI5LjY5LDg1LjM5IDMxLjc0LDMxLjc0IDcwLjAyLDQ1LjA4IDg1LjUzLDI5LjYzIDExMC44OSwtMTEwLjg5IDEyOS42OSwtMjk0LjEzIDUyLjgyLC01MTUuMjR6Ii8+DQogICA8cGF0aCBjbGFzcz0iZmlsNCIgZD0iTTE5MzMuMiA5OS42MWMtMTExLjExLC0xMTEuMTMgLTI5NC4xMywtMTI5Ljk1IC01MTUuNDgsLTUzLjAyIC0yMDEuNTcsNjkuOTYgLTQxNy4yMiwyMTMuMTggLTYwNi45NSw0MDIuODcgLTQxMi43OCw0MTIuNzcgLTU2Ni40NCw5MDUuODMgLTM0OS45NywxMTIyLjQ0IDE1LjQ1LDE1LjMyIDUzLjY3LDIuMTEgODUuNDYsLTI5LjY0IDMxLjg1LC0zMS43MiA0NS4xMywtNzAuMDYgMjkuNjksLTg1LjUxIC0xMjguNTUsLTEyOC4yIC0xNi4wNSwtNTI2LjA3IDM0OS45OCwtODkyLjE1IDE3My4wOSwtMTczLjA5IDM2Ni4zNiwtMzAyLjA3IDU0NS4yMywtMzY0LjI3IDE1Ni44NSwtNTQuMzMgMjgzLjMxLC00OS4xNiAzNDYuOSwxNC4zMSAxNS4zMiwxNS4zMiA1My42NiwyLjExIDg1LjQxLC0yOS43NCAzMS43MywtMzEuODcgNDUuMDcsLTY5Ljk2IDI5Ljc0LC04NS4yOHoiLz4NCiAgPC9nPg0KIDwvZz4NCjwvc3ZnPg0K" alt="FlutterWave Logo" role="presentation" style="width:85px;"
></button>
                </div>
        `;
    }

    bootPayment(event = null) {
        let self = this;
        if (event) {
            event.getClientCredentials(self.getPaymentName(), self.get_request_flow_address, (data) => {
                self.client_id = data?.data;
                event.loadScriptDynamically(self.script_path, 'flutterwave')
                    .then(() => {
                        event.addPaymentButton(self.getPaymentButton());
                        self.initFlutterWaveButton(event);
                    });
            })
        }
    }

    initFlutterWaveButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const cart = new TrackCart();
                    const currency = 'USD';
                    const totalPrice = cart.getTotalItemPrice();
                    const payeeEmail = cart.getCheckOutEmail();

                    if (payeeEmail && payeeEmail.checkValidity()) {
                        cart.removeCheckoutEmailInvalid();
                        event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                            self.invoice_id = data?.data;
                            if (self.invoice_id) {
                                FlutterwaveCheckout({
                                    public_key: self.client_id,
                                    tx_ref: self.invoice_id,
                                    amount: totalPrice,
                                    currency: currency,
                                    payment_options: "card",
                                    callback: function(orderData) {
                                        // Send AJAX verification request to backend
                                        if (orderData.status === 'successful') {
                                            const cart = new TrackCart();
                                            const checkOutEmail = cart.getCheckOutEmail();
                                            const body = {
                                                invoice_id: self.invoice_id,
                                                checkout_email: checkOutEmail.value,
                                                orderData: orderData,
                                                cartItems: Array.from(cart.getCart())
                                            };

                                            event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                                body,
                                                (data) => {
                                                    // Show a success message within this page, e.g.
                                                    const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                    element.innerHTML = '';
                                                    element.innerHTML = data?.message;
                                                    const cart = new TrackCart();
                                                    localStorage.removeItem(cart.getCartStorageKey())
                                                    // Reload TonicsCart Data From LocalStorage
                                                    cart.setCurrentState(cart.ReloadCartFromLocalStorageState);
                                                    cart.runStates();
                                                    // Or go to another URL:  actions.redirect('thank_you.html');
                                                },
                                                (error) => {

                                                });
                                        }
                                    },
                                    onclose: function(incomplete) {
                                        console.log("Closed", incomplete)
                                    },
                                    meta: self.getFlutterWaveItems(cart.getCart(), currency),
                                    customer: {
                                        email: payeeEmail.value
                                    },
                                    customizations: {
                                        title: "Audio Store",
                                    },
                                });
                            } else {
                                reject('Invalid Invoice ID');
                            }

                        }, () => {
                            reject('Something Went Wrong Processing Payment');
                        });
                    } else {
                        cart.addCheckoutEmailInvalid();
                        reject('Invalid Email Address');
                    }
                });
            });
        }
    }

    getFlutterWaveItems(cart, currency = 'USD') {
        let info = '';
        for (let [key, value] of cart.entries()) {
            info += '[ Title:' + value.track_title + ' -> Price: ' + value?.price + ' ]'
        }
        return {
            item_info: info
        }
    }

}

class DefaultTonicsPayPalGateway extends TonicsPaymentEventAbstract {

    invoice_id = null;

    constructor(event) {
        super(event);
    }

    getPaymentName() {
        return "AudioTonicsPayPalHandler";
    }

    getPaymentButton() {
        return `
        <div id="smart-button-container">
            <div style="text-align: center;">
                <div id="paypal-button-container"></div>
            </div>
        </div>
        `;
    }

    bootPayment(event = null) {
        let self = this;
        if (event) {
            event.getClientCredentials(self.getPaymentName(), self.get_request_flow_address, (data) => {
                const clientID = data?.data;
                const currencyName = 'USD';
                event.loadScriptDynamically(`https://www.paypal.com/sdk/js?client-id=${clientID}&enable-funding=venmo&currency=${currencyName}`, 'paypal')
                    .then(() => {
                        event.addPaymentButton(self.getPaymentButton());
                        self.initPayPalButton(event);
                    });
            })
        }
    }

    initPayPalButton(event) {
        let self = this;
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'white',
                layout: 'vertical',
                label: 'pay',
            },

            createOrder: (data, actions) => {
                //Make an AJAX request to the server to generate the invoice_id
                return new Promise((resolve, reject) => {
                    const cart = new TrackCart();
                    const currency = 'USD';
                    const totalPrice = cart.getTotalItemPrice();
                    const payeeEmail = cart.getCheckOutEmail();

                    if (payeeEmail && payeeEmail.checkValidity()) {
                        cart.removeCheckoutEmailInvalid();
                    } else {
                        cart.addCheckoutEmailInvalid();
                        reject('Invalid Email Address');
                    }

                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            resolve(actions.order.create({
                                "purchase_units": [{
                                    "amount": {
                                        "currency_code": currency,
                                        "value": totalPrice,
                                        "breakdown": {
                                            "item_total": {
                                                "currency_code": currency,
                                                "value": totalPrice
                                            }
                                        }
                                    },
                                    "invoice_id": self.invoice_id,
                                    "items": self.getPayPalItems(cart.getCart(), currency)
                                }]
                            }));
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                }).catch(function (error) {
                    console.log("Error creating order: ", error);
                });
            },

            onApprove: (data, actions) => {
                return actions.order.capture().then((orderData) => {

                    if (orderData.status === 'COMPLETED') {
                        const cart = new TrackCart();
                        const checkOutEmail = cart.getCheckOutEmail();
                        const body = {
                            invoice_id: self.invoice_id,
                            checkout_email: checkOutEmail.value,
                            orderData: orderData,
                            cartItems:  Array.from(cart.getCart())
                        };

                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                            body,
                            (data) => {
                                // Show a success message within this page, e.g.
                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                element.innerHTML = '';
                                element.innerHTML = data?.message;
                                const cart = new TrackCart();
                                localStorage.removeItem(cart.getCartStorageKey())
                                // Reload TonicsCart Data From LocalStorage
                                cart.setCurrentState(cart.ReloadCartFromLocalStorageState);
                                cart.runStates();
                                // Or go to another URL:  actions.redirect('thank_you.html');
                            },
                            (error) => {

                            });

                        // Full available details
                        // console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

                    } else {

                    }
                });
            },

            onError: function (err) {
                // console.log('An Error Occured Processing Payment')
                // console.log(err);
            }
        }).render('#paypal-button-container');
    }

    getPayPalItems(cart, currency = 'USD') {
        const items = [];
        for (let [key, value] of cart.entries()) {
            items.push({
                "name": value.track_title,
                "description": `You ordered License ${value.name} with slug ${value.slug_id}`,
                "unit_amount": {
                    "currency_code": currency,
                    "value": value?.price
                },
                "quantity": "1"
            },)
        }

        return items;
    }
}

//----------------------
//--- PAYMENT HANDLERS
//---------------------

function getTonicsCloudCreditAmount() {
    return parseFloat(document.querySelector(`input[name="payment_amount"]`)?.value);
}

function getTonicsCloudCustomerEmail() {
    return document.querySelector(`input[name="customer_email"]`)?.value;
}

function paymentTonicsCloudValidation()
{
    if (getTonicsCloudCreditAmount() < 1){
        window.TonicsScript.infoToast('Payment Amount Must Be At Least $1.00 or Higher', 10000);
        throw new DOMException("Payment Amount Must Be At Least $1.00");
    }
}

class TonicsCloudTonicsPayStackGateway extends DefaultTonicsPayStackWaveGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudPayStackHandler";
    }

    initPayStackButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    const checkOutEmail = getTonicsCloudCustomerEmail();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            const paystack = new PaystackPop();
                            paystack.newTransaction({
                                key: self.client_id,
                                email: checkOutEmail,
                                // PayStack stupid way of storing amount, very stupid
                                currency: 'USD',
                                amount: totalPrice * 100,
                                ref: self.invoice_id,
                                onSuccess: (transaction) => {
                                    // Payment complete! Reference: transaction.reference
                                    // Send AJAX verification request to backend
                                    if (transaction.status === 'success') {
                                        const body = {
                                            invoice_id: self.invoice_id,
                                            amount: totalPrice,
                                            checkout_email: checkOutEmail,
                                            orderData: transaction
                                        };

                                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                            body,
                                            (data) => {
                                                // Show a success message within this page, e.g.
                                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                element.innerHTML = '';
                                                element.innerHTML = data?.message;
                                            },
                                            (error) => {
                                                console.log(error);
                                            });
                                    }
                                },
                                onCancel: () => {
                                    console.log("Closed")
                                }
                            });
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                });
            });
        }
    }
}

class TonicsCloudTonicsFlutterWaveGateway extends DefaultTonicsFlutterWaveGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudFlutterWaveHandler";
    }

    initFlutterWaveButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    const checkOutEmail = getTonicsCloudCustomerEmail();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            FlutterwaveCheckout({
                                public_key: self.client_id,
                                tx_ref: self.invoice_id,
                                amount: totalPrice,
                                currency: currency,
                                payment_options: "card, account, banktransfer",
                                callback: function(orderData) {
                                    // Send AJAX verification request to backend
                                    if (orderData.status === 'successful') {
                                        const body = {
                                            invoice_id: self.invoice_id,
                                            checkout_email: checkOutEmail,
                                            orderData: orderData
                                        };

                                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                            body,
                                            (data) => {
                                                // Show a success message within this page, e.g.
                                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                element.innerHTML = '';
                                                element.innerHTML = data?.message;
                                            },
                                            (error) => {

                                            });
                                    }
                                },
                                onclose: function(incomplete) {
                                    console.log("Closed", incomplete)
                                },
                                customer: {
                                    email: checkOutEmail,
                                },
                            });
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                });
            });
        }
    }
}

class TonicsCloudTonicsPayPalGateway extends DefaultTonicsPayPalGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudPayPalHandler";
    }

    initPayPalButton(event) {
        let self = this;
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'white',
                layout: 'vertical',
                label: 'pay',
            },
            createOrder: (data, actions) => {
                //Make an AJAX request to the server to generate the invoice_id
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            resolve(actions.order.create({
                                "purchase_units": [{
                                    "amount": {
                                        "currency_code": currency,
                                        "value": totalPrice,
                                        "breakdown": {
                                            "item_total": {
                                                "currency_code": currency,
                                                "value": totalPrice
                                            }
                                        }
                                    },
                                    "invoice_id": self.invoice_id
                                }]
                            }));
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });

                }).catch(function (error) {
                    console.log("Error creating order: ", error);
                });
            },

            onApprove: (data, actions) => {
                return actions.order.capture().then((orderData) => {

                    if (orderData.status === 'COMPLETED') {
                        const checkOutEmail = getTonicsCloudCustomerEmail();
                        const body = {
                            invoice_id: self.invoice_id,
                            totalPrice: getTonicsCloudCreditAmount(),
                            checkout_email: checkOutEmail,
                            orderData: orderData
                        };

                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                            body,
                            (data) => {
                                // Show a success message within this page, e.g.
                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                element.innerHTML = '';
                                element.innerHTML = data?.message;
                            },
                            (error) => {

                            });

                        // Full available details
                        // console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

                    } else {

                    }
                });
            },

            onError: function (err) {
                // console.log('An Error Occurred Processing Payment')
                // console.log(err);
            }
        }).render('#paypal-button-container');
    }
}

//---------------------------
//--- HANDLER AND EVENT SETUP
//---------------------------
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnPaymentGatewayCollatorEvent.push(
        ...[
            TonicsCloudTonicsFlutterWaveGateway, TonicsCloudTonicsPayStackGateway, TonicsCloudTonicsPayPalGateway
        ]
    );

    // Fire Payment Gateways
    let OnGatewayCollator = new OnPaymentGatewayCollatorEvent();
    window.TonicsEvent.EventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnGatewayCollator, OnPaymentGatewayCollatorEvent);
}
