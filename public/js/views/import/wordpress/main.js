import * as myModule from "./script-combined.js";
let preElement = document.querySelector('.installation-pre'),
    form = document.querySelector('form'),
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
                        preElement.insertAdjacentHTML('beforeend', `<code>${data} Closed</code>`);
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
                    myModule.errorToast(data.message);
                }
            }
        });
    }
}

function preCodeMessage(message = '') {
    if (preElement){
        preElement.insertAdjacentHTML('beforeend', message)
        if (preElement.lastElementChild){
            preElement.scrollTop = preElement.scrollHeight;
        }
    }
}

function defaultXHR(requestHeaders = {}) {
    let defaultHeader = {
        'Tonics-CSRF-Token': `${myModule.getCSRFFromInput(['tonics_csrf_token', 'csrf_token'])}`
    };
    return new myModule.XHRApi({...defaultHeader, ...requestHeaders});
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