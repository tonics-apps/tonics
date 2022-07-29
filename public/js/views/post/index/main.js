import * as myModule from "./script-combined.js";

try {
    new myModule.MenuToggle('.form-and-filter', new myModule.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Form-Filter")
}

let form = document.querySelector('form');
if (form){
    form.addEventListener('submit', (e) => {
        let target = e.target;
        let inputs = target.querySelectorAll('input');
        if (inputs.length > 0){
            inputs.forEach((input) => {
                let value = input.value;
                value.trim();
                if (!value){
                    input.disabled = true;
                }
            })
        }
    })

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