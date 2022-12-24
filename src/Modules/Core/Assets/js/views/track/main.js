
if (document.querySelector('.audio-player')){
    let audioPlayer = new AudioPlayer();
    audioPlayer.run();
    let parent = '.audio-player-queue-list',
        widgetChild = `.track-in-queue`,
        top = false, bottom = false,
        sensitivity = 0, sensitivityMax = 5;
    new Draggables(parent)
        .settings(widgetChild, ['.track-license'], false) // draggable element
        .onDragDrop(function (element, self) {
            let elementDropped = self.getDroppedTarget().closest(widgetChild);
            let elementDragged = self.getDragging().closest(widgetChild);
            if (elementDropped !== elementDragged && top || bottom){
                // swap element
                swapNodes(elementDragged, elementDropped, self.draggingOriginalRect, () => {
                    audioPlayer.resetQueue();
                });
                sensitivity = 0;
                top = false; bottom = false;
            }
        }).onDragTop((element) => {
        if (sensitivity++ >= sensitivityMax){
            let dragToTheTop = element.previousElementSibling;
            if (dragToTheTop && dragToTheTop.classList.contains('track-in-queue')){
                top = true;
            }
        }
    }).onDragBottom( (element) => {
        if (sensitivity++ >= sensitivityMax){
            let dragToTheBottom = element.nextElementSibling;
            if (dragToTheBottom && dragToTheBottom.classList.contains('track-in-queue')) {
                bottom = true;
            }
        }
    }).run();

    new MenuToggle('.audio-player', new Query())
        .settings('.audio-player-global-container', '.dropdown-toggle', '.audio-player-queue')
        .buttonIcon('#tonics-arrow-down', '#tonics-arrow-up')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .stopPropagation(false)
        .closeOnClickOutSide(false)
        .run();

    new MenuToggle('.time-progress', new Query())
        .settings('.time-progress-marker', '.marker-dropdown-toggle', '.audio-player-marker-data')
        .buttonIcon('#tonics-arrow-down', '#tonics-arrow-up')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .stopPropagation(false)
        .closeOnClickOutSide(false)
        .run();

    new MenuToggle('.audio-player-queue', new Query())
        .settings('.track-in-queue', '.dropdown-toggle', '.track-license')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .stopPropagation(false)
        .closeOnClickOutSide(false)
        .run();
}

/**
 * @param requestHeaders
 * @protected
 */
function defaultXHR(requestHeaders = {}) {
    let defaultHeader = {};
    return new XHRApi({...defaultHeader, ...requestHeaders});
}

let trackMainContainer = document.querySelector('main'),
    licenseIDMap = new Map(),
    windowInstanceForDownloadURL = null,
    inputLicenseDownloadURL = null,
    selectedLicense = null,
    licenseDownloadsContainer = null;

if (trackMainContainer) {
    trackMainContainer.addEventListener('click', (e) => {
        let el = e.target;
        // MORE BUTTON
        if (el.classList.contains('more-button')) {
            e.preventDefault();
            let action = el.dataset.action,
                url = el.dataset.morepageurl;
            defaultXHR(el.dataset).Get(url, function (err, data) {
                if (data) {
                    data = JSON.parse(data);
                    if (data.hasOwnProperty('status') && data.status === 200) {
                        let ul = el.closest('.menu-box-radiobox-items'),
                            moreButton = ul.querySelector('.more-button'),
                            lastMenuItem = ul.querySelector('li:nth-last-of-type(1)');
                        if (moreButton) {
                            moreButton.remove();
                        }
                        lastMenuItem.insertAdjacentHTML('afterend', data.data);
                    }
                }
            });
        }

        // License Selector Download URL
        if (el.classList.contains('upload-license-download-url')) {
            inputLicenseDownloadURL = el.parentElement.querySelector('.input-license-download-url');
            if (tonicsFileManagerURL) {
                let windowFeatures = "left=95,top=100";
                windowInstanceForDownloadURL = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
            }
        }
    });

    window.addEventListener('message', (e) => {
        if (e.origin !== siteURL) {
            return;
        }
        let data = e.data;
        if (data.hasOwnProperty('cmd') && data.cmd === "tonics:RegularLink") {
            if (inputLicenseDownloadURL) {
                inputLicenseDownloadURL.value = data.value;
                windowInstanceForDownloadURL.close();
            }
        }
    });

    // For SEARCH
    let searchMenuBoxItem = document.querySelectorAll('.menu-box-item-search'),
        searchBoxInitials = [];

    searchMenuBoxItem.forEach(((value, key) => {
        searchBoxInitials[value.dataset.menuboxname] = value.parentElement.cloneNode(true);
    }));

    trackMainContainer.addEventListener('keydown', (e) => {
        let el = e.target;
        if (el.classList.contains('menu-box-item-search')) {
            let value = el;
            if (e.code === 'Enter') {
                e.preventDefault();
                let searchInputValue = value.value;
                searchInputValue = searchInputValue.trim();
                if (searchInputValue.length > 0 && value.dataset.hasOwnProperty('searchvalue')) {
                    value.dataset.searchvalue = searchInputValue;
                    let url = value.dataset.query + encodeURIComponent(searchInputValue);
                    defaultXHR(value.dataset).Get(url, function (err, data) {
                        if (data) {
                            data = JSON.parse(data);
                            if (data.hasOwnProperty('status') && data.status === 200) {
                                let parentElement = value.parentElement;
                                let realSearchInput = value.cloneNode(true);
                                value.parentElement.innerHTML = data.data;
                                parentElement.prepend(realSearchInput);
                            }
                        }
                    });
                }
            }
        }
    })

    trackMainContainer.addEventListener('change', (e) => {
        let el = e.target;
        // License Selector
        if (el.classList.contains('license-selector')) {
            getLicenseDownloadInfo(el);
        }
    });

    trackMainContainer.addEventListener('input', (e) => {
        let el = e.target,
            value = el;
        if (el.classList.contains('menu-box-item-search')) {
            e.preventDefault();
            let searchInputValue = value.value;
            searchInputValue = searchInputValue.trim();
            if (searchInputValue === "") {
                let parentElement = value.parentElement;
                parentElement.innerHTML = searchBoxInitials[value.dataset.menuboxname].innerHTML;
            }
        }
    });
}

let licenseSelector = document.querySelector('.license-selector');
if (licenseSelector && licenseSelector.dataset.new === 'true') {
    getLicenseDownloadInfo(licenseSelector);
}

function getLicenseDownloadInfo(el) {
    licenseDownloadsContainer = el.closest('li').querySelector('.license-downloads');
    selectedLicense = el.options[el.selectedIndex];
    selectedLicense.dataset.licenseID = selectedLicense.value;
    if (licenseIDMap.has(selectedLicense.value)) {
        if (licenseDownloadsContainer) {
            licenseDownloadsContainer.innerHTML = '';
            licenseDownloadsContainer.innerHTML = licenseIDMap.get(selectedLicense.value);
            return;
        }
    }
    defaultXHR(selectedLicense.dataset).Get(window.location.href, function (err, data) {
        if (data) {
            data = JSON.parse(data);
            if (data.hasOwnProperty('status') && data.status === 200) {
                if (licenseDownloadsContainer) {
                    licenseDownloadsContainer.innerHTML = '';
                    licenseDownloadsContainer.innerHTML = data.data;
                    licenseIDMap.set(selectedLicense.value, data.data);
                }
            }
        }
    });
}