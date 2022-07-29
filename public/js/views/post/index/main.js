import * as myModule from "./script-combined.js";

let form = document.querySelector('form');
if (form){
    form.addEventListener('click', (e) => {
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
    });
}

/**
 * @param requestHeaders
 * @protected
 */
function defaultXHR(requestHeaders = {}) {
    let defaultHeader = {};
    return new XHRApi({...defaultHeader, ...requestHeaders});
}