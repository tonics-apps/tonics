/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

try {
    // For Filter Options
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For More Filter Options
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.form-and-filter', '.more-filter-button-toggle', '.more-filter-container')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // Filter For Download or Buy
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.tonics-file', '.audioplayer-track-download-buy-button', '.track-download-buy-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For Read More Container
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.tonics-folder-about-container', '.read-more-button', '.tonics-track-content')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd"])
        .menuIsOn(["swing-in-top-fwd"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For Cart Toggle
    window.TonicsScript.MenuToggle('.tonics-cart-container', window.TonicsScript.Query())
        .settings('.cart-button-counter', '.cart-button', '.cart-child-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .stopPropagation(true)
        .propagateElements(['[data-tonics_navigate]'])
        .run();

} catch (e) {
    console.error("An Error Occur Setting MenuToggle: Track Audio Page")
}


const selectElementsForm = document.querySelector("form");
if (selectElementsForm) {
    selectElementsForm.addEventListener("submit", function (event) {
        const inputElements = this.querySelectorAll("input, select");
        inputElements.forEach(inputElement => {
            if (inputElement.value === "") {
                inputElement.removeAttribute("name");
            }
        });
    });
}

function initRouting(containerSelector, navigateCallback = null) {
    const container = document.querySelector(containerSelector);

    function callCallback(options) {
        if (navigateCallback) {
            navigateCallback(options);
        }
    }

    function navigate(url) {
        callCallback({url, type: 'before'});
        // Push a new history entry with the url
        window.history.pushState({'url': url}, '', url);
        callCallback({url, type: 'after'});
    }

    window.onload = () => {
        // Perform initialization or setup
        // without the below, the popstate won't fire if user uses the back button for the first time
        window.history.replaceState({url: window.location.pathname}, '', window.location.pathname);
    };

    // Bind a popstate event listener to enable the back button
    window.addEventListener('popstate', (event) => {
        if (event.state) {
            let url = event.state.url;
            callCallback({url, type: 'popstate'});
            // we only navigate in a pop state if the url is not the same, without doing this, the forward button won't work
            // because there won't be anywhere to navigate to

            // Check if the URL is a relative URL
            if (!url.startsWith('http')) {
                // Convert the relative URL to an absolute URL using the new URL constructor
                url = new URL(url, window.location.href).href;
            }

            // Parse the URL using the URL interface
            const parsedUrl = new URL(url);
            // Compare the pathname and search properties of the parsed URL to the window.location object
            if (window.location.pathname !== parsedUrl.pathname || window.location.search !== parsedUrl.search) {
                navigate(url);
            }
        }
    })

    // Bind a click event listener to the container using event delegation
    container.addEventListener('click', e => {
        const el = e.target;
        if (el.closest('[data-tonics_navigate]')) {
            e.preventDefault();
            let element = el.closest('[data-tonics_navigate]');
            let url = element.getAttribute('data-url_page');
            const loading = element.querySelector('.svg-per-file-loading');
            if (loading) {
                loading.classList.remove('d:none');
            }
            navigate(url);
        }

        if (el.closest('.tonics-submit-button') && el.closest('.form-and-filter')) {
            e.preventDefault();
            const form = el.closest('.form-and-filter');
            // Get the form data
            const formData = new FormData(form);
            // Construct the query string using the URLSearchParams interface
            const params = new URLSearchParams();
            for (const [key, value] of formData) {
                // Trim the value before adding it to the query string
                if (value.trim()) {
                    const trimmedValue = value.trim();
                    params.set(key, trimmedValue);
                }
            }
            const queryString = params.toString();
            // if queryString is not empty
            if (queryString) {
                // Append the query string to the URL
                const newUrl = window.location.pathname + '?' + queryString;
                navigate(newUrl);
            }
        }

    });
}

// Initialize the routing for the tonics-file-container element
initRouting('body', ({url, type}) => {

    if (type === 'after' || type === 'popstate') {
        window.TonicsScript.XHRApi({isAPI: true, type: 'isTonicsNavigation'}).Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (data.data?.isFolder) {
                    tonicsAudioNavForFolder(data, url);
                }
                if (data.data?.isTrack) {
                    tonicsAudioNavForTrack(data, url);
                }
            }
        });
    }
});

function tonicsAudioNavForFolder(data, url) {
    let tonicsFolderMain = document.querySelector('.tonics-folder-main'),
        beforeFolderSearchLoading = document.querySelector('.before-folder-search'),
        tonicsFolderAboutContainer = document.querySelector('.tonics-folder-about-container'),
        tonicsFolderSearch = document.querySelector('.tonics-folder-search');

    if (tonicsFolderMain && data.data?.fragment) {
        tonicsFolderMain.innerHTML = data?.data.fragment;
        document.title = data?.data.title;
        if (tonicsFolderSearch) {
            tonicsFolderSearch.remove();
            tonicsFolderAboutContainer.remove();
        }

        if (beforeFolderSearchLoading) {
            beforeFolderSearchLoading.classList.remove('d:none');
            window.TonicsScript.XHRApi({isAPI: true, type: 'isSearch'}).Get(url, function (err, data) {
                data = JSON.parse(data);
                beforeFolderSearchLoading.classList.add('d:none');
                beforeFolderSearchLoading.insertAdjacentHTML('beforebegin', data?.data);
            });
        }
    }
}

function tonicsAudioNavForTrack(data, url) {
    let tonicsFolderMain = document.querySelector('.tonics-folder-main'),
        tonicsFolderAboutContainer = document.querySelector('.tonics-folder-about-container'),
        tonicsFolderSearch = document.querySelector('.tonics-folder-search');
    if (tonicsFolderMain && data.data?.fragment) {
        tonicsFolderMain.innerHTML = data?.data.fragment;
        document.title = data?.data.title;
        if (tonicsFolderSearch) {
            tonicsFolderSearch.remove();
            tonicsFolderAboutContainer.remove();
        }
    }
}

const tonicsCartSectionContainer = document.querySelector('.tonics-cart-container');
if (tonicsCartSectionContainer) {
    tonicsCartSectionContainer.addEventListener('click', (e) => {
        let el = e.target;
        if (el.closest('.tonics-remove-cart-item')) {
            let trackCart = new TrackCart();
            trackCart.cartItemToRemove = el.closest('.cart-item[data-slug_id]');
            trackCart.setCurrentState(trackCart.RemoveItemFromCartState);
            trackCart.runStates();
        }

        const cartButtonCounterEl = el.closest('.cart-button-counter');
        if (cartButtonCounterEl && !cartButtonCounterEl.dataset.hasOwnProperty('tonics_loaded_payment_gateway')) {
            cartButtonCounterEl.dataset.tonics_loaded_payment_gateway = ' true';
            // Fire Payment Gateways
            let OnAudioPlayerPaymentGatewayCollator = new OnAudioPlayerPaymentGatewayCollatorEvent();
            window.TonicsEvent.EventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnAudioPlayerPaymentGatewayCollator, OnAudioPlayerPaymentGatewayCollatorEvent);
        }

    });
}

// Reload TonicsCart Data From LocalStorage
let trackCart = new TrackCart();
trackCart.setCurrentState(trackCart.ReloadCartFromLocalStorageState);
trackCart.runStates();

//----------------
//--- HANDLERS
//----------------

class TonicsAudioPlayHandler {
    constructor(event) {
        const songData = event._songData;
        const url_page = songData?.url_page;
        const url_page_el = document.querySelector(`button[data-url_page="${url_page}"]`);
        if (url_page_el.closest('[data-tonics-audioplayer-track]') && !songData.hasOwnProperty('markers')) {
            window.TonicsScript.XHRApi({isAPI: true, type: 'getMarker'}).Get(url_page, function (err, data) {
                data = JSON.parse(data);
                if (data?.data?.markers) {
                    songData.markers = data.data.markers;
                    event._songData = songData;
                    if (songData._self && songData?.markers.length > 0) {
                        songData.markers.forEach((marker) => {
                            if (marker.track_marker_start) {
                                const markerPercentageAndSec = songData._self.getMarkerPercentageAndSeconds(marker.track_marker_start, songData.howl.duration());
                                markerPercentageAndSec.text = marker.track_marker_name;
                                marker._track_marker_start_info = markerPercentageAndSec;
                            }

                            if (marker.track_marker_end) {
                                const markerPercentageAndSec = songData._self.getMarkerPercentageAndSeconds(marker.track_marker_end, songData.howl.duration());
                                markerPercentageAndSec.text = marker.track_marker_name;
                                marker._track_marker_end_info = markerPercentageAndSec;
                            }
                        });
                        songData._self.handleMarkerUpdating();
                    }
                }
            });
        }
    }
}

class TonicsAudioPlayerClickHandler {
    constructor(event) {
        let trackCart = new TrackCart();
        const el = event._eventEl;
        let trackDownloadContainer = el.closest('.tonics-file')?.querySelector('.track-download-ul-container');
        let self = this;
        // download_buy_container
        if (el.dataset.hasOwnProperty('download_buy_button') && el.dataset.hasOwnProperty('licenses')) {
            let licenses = el.dataset.licenses;

            if (trackDownloadContainer) {
                if (trackDownloadContainer.dataset.license_loaded === 'false') {
                    trackDownloadContainer.dataset.license_loaded = 'true';
                    licenses = JSON.parse(licenses);
                    licenses.forEach((license) => {
                        trackDownloadContainer.insertAdjacentHTML('beforeend', this.trackDownloadList(license))
                    });
                }

                if (trackDownloadContainer.dataset.license_loaded === 'true') {
                    trackCart.setCurrentState(trackCart.UpdateCartIconAdditionToTheCartMenuState, trackDownloadContainer);
                    trackCart.runStates();
                }
            }
        }

        if (el.dataset.hasOwnProperty('remove_from_cart')) {
            trackCart.setCurrentState(trackCart.RemoveItemFromCartWithUniqueID, el);
            trackCart.runStates();
            return;
        }

        if (el.dataset.hasOwnProperty('indie_license')) {
            if (el.dataset.hasOwnProperty('indie_license_type_is_free')) {
                let trackItem = el.closest('[data-url_page]'),
                    urlPage = trackItem?.dataset?.url_page,
                    slugID = trackItem?.dataset?.slug_id;

                let dataSet = JSON.stringify({urlPage, slugID, dataset: el.dataset.indie_license});

                window.TonicsScript.XHRApi({
                    isAPI: true,
                    type: 'freeTrackDownload',
                    freeTrackData: dataSet
                }).Get(urlPage, function (err, data) {
                    if (data) {
                        data = JSON.parse(data);
                        if (data?.data?.artifact) {
                            // Issue a download link
                            self.openDownloadLink(data.data.artifact);
                        }
                    }
                });
            } else {
                let trackItem = el.closest('[data-slug_id]');
                let trackSlugID = trackItem?.dataset?.slug_id;
                let trackURLPage = trackItem?.dataset?.url_page;
                let trackTitle = trackItem?.dataset?.audioplayer_title;
                let trackImage = trackItem?.dataset?.audioplayer_image;
                let indieLicense = JSON.parse(el.dataset.indie_license);
                if (trackSlugID) {
                    indieLicense.slug_id = trackSlugID;
                    indieLicense.track_title = trackTitle;
                    indieLicense.track_image = trackImage;
                    indieLicense.url_page = trackURLPage;
                    trackCart.licenseData = indieLicense;
                    trackCart.setCurrentState(trackCart.InitialState);
                    trackCart.runStates();

                    // Add Remove Button
                    trackCart.setCurrentState(trackCart.UpdateCartIconAdditionToTheCartMenuState, trackDownloadContainer);
                    trackCart.runStates();
                }
            }
        }
    }

    openDownloadLink(link) {
        window.open(link, "_blank");
    }

    trackDownloadList(data) {
        let price = parseInt(data.price),
            name = data.name,
            currency = '$',
            uniqueID = data.unique_id;
        let encodeData = JSON.stringify(data);

        if (data?.is_enabled === '1') {
            if (price > 0) {
                return `
<li class="download-li">
    <span class="text cart-license-price">${name}<span> (${currency}${price}) → </span></span>
    <button type="button" title="Add (${name} License) To Cart" data-unique_id="${uniqueID}" data-indie_license='${encodeData}' class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:white">
                <svg class="icon:audio tonics-cart-icon tonics-widget pointer-events:none"><use class="svgUse" xlink:href="#tonics-cart"></use>
     </button>
</li>`;
            } else {
                return `
<li class="download-li">
    <span class="text cart-license-price">${name}<span> (Free) → </span></span>
    <button type="button" title="Download ${name}" data-unique_id="${uniqueID}" data-indie_license_type_is_free="true" 
    data-indie_license='${encodeData}' class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:white">
                <svg class="icon:audio tonics-cart-icon tonics-widget pointer-events:none"><use class="svgUse" xlink:href="#tonics-download"></use>
     </button>
</li>`;
            }
        }

        return '';
    }

}


//----------------------
//--- PAYMENT EVENTS
//---------------------

class TonicsPaymentEventAbstract {

    getPaymentName() {
    }

    getPaymentButton() {

    }

    bootPayment() {

    }
}

class OnAudioPlayerPaymentGatewayCollatorEvent {

    get_request_flow_address = "/tracks_payment/get_request_flow";
    post_request_flow_address = "/tracks_payment/post_request_flow";

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

    generateInvoiceID(PaymentHandlerName, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "GenerateInvoiceID"
        }).Get(this.get_request_flow_address, function (err, data) {
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

    getClientCredentials(PaymentHandlerName, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "ClientPaymentCredentials"
        }).Get(this.get_request_flow_address, function (err, data) {
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

    sendBody(PaymentHandlerName, BodyData, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "CapturedPaymentDetails",
            'Tonics-CSRF-Token': `${this.getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
        }).Post(this.post_request_flow_address, JSON.stringify(BodyData), function (err, data) {
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
     * e.g tonics, this is useful for preventing the script from loading twice
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
}

//----------------------
//--- PAYMENT HANDLERS
//---------------------

class TonicsPayPalGateway extends TonicsPaymentEventAbstract {
    invoice_id = null;

    constructor(event) {
        super();
        this.bootPayment(event);
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
            event.getClientCredentials(self.getPaymentName(), (data) => {
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

                    event.generateInvoiceID(self.getPaymentName(), (data) => {
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

                        event.sendBody(self.getPaymentName(),
                            body,
                            (data) => {
                                console.log(data);
                                // Show a success message within this page, e.g.
                                const element = document.getElementById('paypal-button-container');
                                element.innerHTML = '';
                                element.innerHTML = data?.message;
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
                "description": `At the time of the purchase, you bought the ${value.name} License of ${value.track_title} wih the slug id ${value.slug_id}`,
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

//---------------------------
//--- HANDLER AND EVENT SETUP
//---------------------------
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnAudioPlayerPlayEvent.push(
        ...[
            TonicsAudioPlayHandler
        ]
    );

    window.TonicsEvent.EventConfig.OnAudioPlayerClickEvent.push(
        ...[
            TonicsAudioPlayerClickHandler
        ]
    );

    window.TonicsEvent.EventConfig.OnAudioPlayerPaymentGatewayCollatorEvent.push(
        ...[
            TonicsPayPalGateway
        ]
    );
}

