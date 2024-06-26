

/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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
    let searchBoxInitials = [];
    trackMainContainer.addEventListener('keydown', (e) => {
        let el = e.target;
        if (el.classList.contains('menu-box-item-search')) {
            let value = el;
            if (e.code === 'Enter') {
                // clone the loaded checkboxes, or radios
                searchBoxInitials[el.dataset.menuboxname] = el.parentElement.cloneNode(true);

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
                // Find all the selected checkbox elements
                let selectedCheckboxes = value.parentElement.querySelectorAll('input[type="checkbox"]:checked');
                if (selectedCheckboxes.length > 0) {
                    let newInnerHTML = '';
                    // Add each selected checkbox element to the beginning of the innerHTML, if it is not already present
                    for (let i = 0; i < selectedCheckboxes.length; i++) {
                        let selectCheckboxValue = selectedCheckboxes[i].value;
                        let checkbox = searchBoxInitials[value.dataset.menuboxname].querySelector(`input[type="checkbox"][value="${selectCheckboxValue}"]`);
                        if (!checkbox){
                            newInnerHTML += selectedCheckboxes[i].parentElement.outerHTML;
                        } else if (checkbox && !checkbox.checked){
                            newInnerHTML += selectedCheckboxes[i].parentElement.outerHTML;
                        } else {
                        }
                    }
                    let initialElements = searchBoxInitials[value.dataset.menuboxname];
                    // Find the first li element
                    let firstLi = initialElements.querySelector('li');
                    // Insert the newInnerHTML string before the first li element
                    firstLi.insertAdjacentHTML('beforebegin', newInnerHTML);

                    let parentElement = value.parentElement;
                    parentElement.innerHTML = initialElements.innerHTML;
                }
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