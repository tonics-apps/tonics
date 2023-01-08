
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
    window.TonicsScript.MenuToggle('.main-tonics-folder-container',  window.TonicsScript.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For More Filter Options
    window.TonicsScript.MenuToggle('.main-tonics-folder-container',  window.TonicsScript.Query())
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
            element.querySelector('.svg-per-file-loading').classList.remove('d:none');
            navigate(url);
        }
    });
}

// Initialize the routing for the tonics-file-container element
initRouting('.main-tonics-folder-container', ({ url, type }) => {
    let tonicsFolderMain = document.querySelector('.tonics-folder-main');
    let beforeFolderSearchLoading = document.querySelector('.before-folder-search');
    let tonicsFolderSearch = document.querySelector('.tonics-folder-search');

    if (type === 'after' || type === 'popstate'){
        window.TonicsScript.XHRApi({isAPI: true, type: 'isTonicsNavigation'}).Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                    if (data.data?.isFolder && tonicsFolderMain && data.data?.fragment){
                        tonicsFolderMain.innerHTML = data?.data.fragment;
                        document.title = data?.data.title;
                        if (tonicsFolderSearch){ tonicsFolderSearch.remove(); }
                        if (beforeFolderSearchLoading){
                            beforeFolderSearchLoading.classList.remove('d:none');
                            window.TonicsScript.XHRApi({isAPI: true, type: 'isSearch'}).Get(url, function (err, data) {
                                data = JSON.parse(data);
                                beforeFolderSearchLoading.classList.add('d:none');
                                beforeFolderSearchLoading.insertAdjacentHTML('beforebegin', data?.data);
                            });
                        }
                    }
            }
        });
    }
});