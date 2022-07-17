import * as myModule from "./script-combined.js";

if (typeof tonicsFileManagerURL === "undefined") {
    window.tonicsFileManagerURL = window.parent.tonicsFileManagerURL;
}

if (typeof siteURL === "undefined") {
    window.siteURL = window.parent.siteURL;
}

if (typeof siteTimeZone === "undefined") {
    window.siteTimeZone = window.parent.siteTimeZone;
}

let chooseMenuFields = document.querySelector('.choose-field-button');
if (chooseMenuFields) {
    chooseMenuFields.addEventListener('click', (e) => {
        let selectedFields = document.querySelectorAll('[data-selected="true"]'),
            selectedFieldSlug = [];
        selectedFields.forEach((field) => {
            selectedFieldSlug.push(field.dataset.field_id);
        });
        if (selectedFieldSlug.length > 0) {
            let slug = {
                action: 'getFieldItems',
                fieldSlug: JSON.stringify(selectedFieldSlug)
            }
            let url = window.location.href + "?action=getFieldItems";
            new XHRApi({...{}, ...slug}).Get(url, function (err, data) {
                if (data) {
                    data = JSON.parse(data);
                    window.parent.postMessage({
                        mceAction: 'execCommand',
                        cmd: 'tonics:FieldSelectedData',
                        value: data.data
                    }, siteURL);
                }
            });
        }
    });
}

if (parent.tinymce && parent.tinymce.activeEditor) {
    window.tinymce = parent.tinymce;
    try {
        let tinyEntryContent = tinymce.activeEditor.dom.select('.entry-content')[0];
        let tinyMenuToggle = new myModule.MenuToggle('.menu-arranger', new myModule.Query().setQueryResult(tinyEntryContent));
        tinyMenuToggle.setQueryResult(tinyEntryContent);
        tinyMenuToggle
            .settings('.menu-arranger', '.dropdown-toggle', '.menu-widget-information')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();
    } catch (e) {
     //   console.log("Can't set MenuToggle: menu-widget or menu-arranger");
    }
}


