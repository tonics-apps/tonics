
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

const router = new Navigo('/');
router.on('/tracks',  () => {
    console.log('Navigating To Tracks')
    // do something
});

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


router.on('/track_categories/:slug_id/:track_cat_slug', ({ data, e }) => {
  //  console.log(data, e); // { slug_id: 'xxx', track_cat_slug: 'save' }
}, {
    before(done, match) {
        let slug_id = match.data?.slug_id
        let el = tonicsFileContainerForAudioPlayer.querySelector(`[data-slug_id="${slug_id}"]`);
        if (el){
            let url = el.dataset.url_page;
            el.querySelector('.svg-per-file-loading').classList.remove('d:none');

            let defaultHeader = {
                type: 'track_category',
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
});


router.resolve();