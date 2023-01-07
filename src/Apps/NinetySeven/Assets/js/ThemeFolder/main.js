
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
    window.TonicsScript.MenuToggle('.form-and-filter',  window.TonicsScript.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For More Filter Options
    window.TonicsScript.MenuToggle('.form-and-filter',  window.TonicsScript.Query())
        .settings('.form-and-filter', '.more-filter-button-toggle', '.more-filter-container')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Form-Filter")
}


let tonicsFileContainerForAudioPlayer = document.querySelector('.tonics-files-container');

const selectElementsForm = document.querySelector("form");
if (selectElementsForm){
    selectElementsForm.addEventListener("submit", function(event) {
        const inputElements = this.querySelectorAll("input, select");
        inputElements.forEach(inputElement => {
            if (inputElement.value === "") {
                inputElement.removeAttribute("name");
            }
        });
    });
}

if (tonicsFileContainerForAudioPlayer){

    /*    router.on('/track_categories/:slug_id/:track_cat_slug', ({ data, e }) => {
            // console.log(data, e); // { slug_id: 'xxx', track_cat_slug: 'save' }
        }, {
            before(done, match) {
                let slug_id = match.data?.slug_id
                let el = tonicsFileContainerForAudioPlayer.querySelector(`[data-slug_id="${slug_id}"]`);
                console.log(el, match);
                if (el){
                    let url = el.dataset.url_page;
                    el.querySelector('.svg-per-file-loading').classList.remove('d:none');

                    let defaultHeader = {
                        type: 'track_category',
                        isAPI: true,
                    };

                    if(url){
                        new XHRApi(defaultHeader).Get(url, {}, function (err, data) {
                            if (data) {
                                data = JSON.parse(data);
                            }
                        });
                    }

                    throw new Error();

                    // Remove The Loader After Receiving The Data From API
                    el.querySelector('.svg-per-file-loading').classList.add('d:none');
                    done();
                }
            }
        });*/
}

function initRouting(containerSelector, navigateCallback = null) {
    const container = document.querySelector(containerSelector);

    function callCallback(options) {
        if (navigateCallback) {
            navigateCallback(options);
        }
    }

    function navigate(url) {
        callCallback({ url, type: 'before' });
        // Push a new history entry with the url
        window.history.pushState({ 'url': url }, '', url);
        callCallback({ url, type: 'after' });
    }

    window.onload = () => {
        // Perform initialization or setup
        // without the below, the popstate won't fire if user uses the back button for the first time
        window.history.replaceState({ url: window.location.pathname }, '', window.location.pathname);
    };

    // Bind a popstate event listener to enable the back button
    window.addEventListener('popstate', (event) => {
        if (event.state) {
            let url = event.state.url;
            callCallback({ url, type: 'popstate' });
            // we only navigate in a pop state if the url is not the same, without doing this, the forward button won't work
            // because there won't be anywhere to navigate to
            if (window.location.pathname !== url){
                navigate(url);
            }
        }
    })

    // Bind a click event listener to the container using event delegation
    container.addEventListener('click', e => {
        const el = e.target;
        e.preventDefault();
        if (el.closest('[data-tonics_navigate]')) {
            let element = el.closest('[data-tonics_navigate]');
            let url = element.getAttribute('data-url_page');
            navigate(url);
        }
    });
}

// Initialize the routing for the tonics-file-container element
initRouting('.tonics-files-container', ({ url, type }) => {
    console.log(`Navigating to ${url} (${type})`);
});

/*
initRouting('.tonics-files-container', (url) => {
        console.log('This is before navigation', url)
    },
    (url) => {
        console.log('This is after navigation', url)
    });*/
