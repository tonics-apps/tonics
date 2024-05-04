
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

const MESSAGE_LIMIT = 200;
let messageInserted = 0;

let preElement = document.querySelector('.installation-pre'),
    form = document.getElementById('ImportForm'),
    importButton = document.querySelector('.import-button'),
    adminPostWidget = document.querySelector('.admin-post-writing-experience'),
    windowInstanceForDownloadURL = null,
    inputLicenseDownloadURL = null;

function submitForm(event) {
    if (document.forms.hasOwnProperty('ImportForm')) {
        event.preventDefault();
        let formEl = document.forms.ImportForm;
        let formData = new FormData(formEl);
        let formEntries = {};
        // Arrange The Form Data
        formData.forEach(function (value, key, parent) {
            formEntries[key] = value;
        });

        let importerLink = window.location.href;
        defaultXHR().Post(importerLink, JSON.stringify(formEntries), function (err, data) {
            if (data) {
               data = JSON.parse(data);
                if (data.hasOwnProperty('status') && data.status === 200) {
                    let eventSource = new EventSource('/admin/tools/imports/wordpress-events');

                    // would have used error instead issue but there is already a built-in type named error
                    eventSource.addEventListener('issue', function(e) {
                        importButton.classList.remove('d:none');
                        try{
                            const data = JSON.parse(e.data);
                            preCodeMessage(`<code class='color:red'>${data}</code>`);
                        }catch(e){
                            preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`);
                        }
                    }, false);

                    eventSource.addEventListener('message', function(e) {
                        try{
                            const data = JSON.parse(e.data);
                            preCodeMessage(`<code>${data}</code>`);
                        }catch(e){
                            preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`);
                        }
                    }, false);

                    eventSource.addEventListener('close', function(e) {
                        importButton.classList.remove('d:none');
                        const data = JSON.parse(e.data);
                        preCodeMessage(`<code>${data} Closed</code>`);
                        eventSource.close();
                        eventSource = null;
                    }, false);

                    eventSource.addEventListener('open', function(e) {
                        importButton.classList.add('d:none');
                        // Connection was opened.
                        console.log("Opening new connection");
                    }, false);

                    eventSource.addEventListener('redirect', function(e) {
                        try{
                            let url = JSON.parse(JSON.parse(e.data));
                            window.location.href = window.location.origin + url.page;
                        }catch(e){
                            preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`);
                        }
                    }, false);
                } else {
                    errorToast(data.message);
                }
            }
        });
    }
}


function preCodeMessage(message = '') {
    if (preElement){
        preElement.insertAdjacentHTML('beforeend', message);
        messageInserted = messageInserted + 1;
        // This improves performance sort of
        if (messageInserted > MESSAGE_LIMIT){
            preElement.firstChild.remove();
        }
        if (preElement.lastElementChild){
            preElement.scrollTop = preElement.scrollHeight;
        }
    }
}

function defaultXHR(requestHeaders = {}) {
    let defaultHeader = {
        'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token'])}`
    };
    return new XHRApi({...defaultHeader, ...requestHeaders});
}

form.addEventListener('submit', submitForm);

if (adminPostWidget) {
    adminPostWidget.addEventListener('click', (e) => {
        let el = e.target;
        // License Selector Download URL
        if (el.classList.contains('upload-license-download-url')) {
            inputLicenseDownloadURL = el.parentElement.querySelector('.input-license-download-url');
            if (tonicsFileManagerURL) {
                let windowFeatures = "left=100,top=100";
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
}