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

   var getCSRFFromInput = function getCSRFFromInput(csrfNames) {

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

        // Get the query string from the URL
        const UrlPlusQueryString = window.location.pathname + window.location.search;
        // Replace the current state of the browser history with the current URL, including the query string
        window.history.replaceState({url: UrlPlusQueryString}, '', UrlPlusQueryString);
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
    let isClicked = false;
    container.addEventListener('click', e => {

        if (isClicked) return;
        isClicked = true;
        setTimeout(() => {
            isClicked = false;
        }, 800); // Set the time to wait before allowing another click, in milliseconds

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
                    params.append(key, trimmedValue);
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

    let mainTonicsFolderContainer = document.querySelector('.main-tonics-folder-container'),
        tonicsFolderMain = document.querySelector('.tonics-folder-main'),
        beforeFolderSearchLoading = document.querySelector('.before-folder-search'),
        tonicsFolderAboutContainer = document.querySelector('.tonics-folder-about-container'),
        tonicsFolderSearch = document.querySelector('.tonics-folder-search');

    if (tonicsFolderAboutContainer){
        tonicsFolderAboutContainer.remove();
    }

    if (tonicsFolderMain && data.data?.fragment) {
        tonicsFolderMain.innerHTML = data?.data.fragment;
        document.title = data?.data.title;
    }

    if (tonicsFolderSearch) {
        tonicsFolderSearch.remove();
    }

    if (beforeFolderSearchLoading){
        beforeFolderSearchLoading.classList.remove('d:none');
    }

    if (mainTonicsFolderContainer){
        window.TonicsScript.XHRApi({isAPI: true, type: 'isSearch'}).Get(url, function (err, data) {
            data = JSON.parse(data);
            if (beforeFolderSearchLoading){
                beforeFolderSearchLoading.classList.add('d:none');
            }
            mainTonicsFolderContainer.insertAdjacentHTML('afterbegin', data?.data);
        });
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
            let OnGatewayCollator = new OnPaymentGatewayCollatorEvent();
            window.TonicsEvent.EventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnGatewayCollator, OnPaymentGatewayCollatorEvent);
        }
    });
}

// Reload TonicsCart Data From LocalStorage
let trackCart = new TrackCart();
/*trackCart.on('stateSwitched', (stateName, stateResult) => {
    // if state is...
    if (trackCart.UpdateCartLicenseInfo === stateName){
        console.log(`state ${stateName}`);
    }
});*/
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
        const url_page_el_from_href = document.querySelector(`a[data-url_page="${url_page}"]`);

        if (url_page_el.closest('[data-tonics-audioplayer-track]') && !songData.hasOwnProperty('markers')) {
            window.TonicsScript.XHRApi({isAPI: true, type: 'getMarker'}).Get(url_page, function (err, data) {
                data = JSON.parse(data)
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

        if (url_page_el_from_href?.dataset){
            this.updateTrackPlays(url_page_el_from_href.dataset);
        }

    }

    updateTrackPlays(BodyData) {
        const url_track_update = "/modules/track/player/update_plays"
        window.TonicsScript.XHRApi({
            'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
        }).Post(url_track_update, JSON.stringify(BodyData), function (err, data) {
            if (data) {
                data = JSON.parse(data);
            }
            if (err) {

            }
        });
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
        // The Price is only for mere display
        let price = parseFloat(data.price),
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
//--- PAYMENT HANDLERS
//---------------------

class TrackTonicsFlutterWaveGateway extends DefaultTonicsFlutterWaveGateway{}

class TrackTonicsPayPalGateway extends DefaultTonicsPayPalGateway {}

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

    window.TonicsEvent.EventConfig.OnPaymentGatewayCollatorEvent.push(
        ...[
            TrackTonicsFlutterWaveGateway, TrackTonicsPayPalGateway
        ]
    );
}
