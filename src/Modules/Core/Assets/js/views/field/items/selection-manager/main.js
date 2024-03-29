if (typeof tonicsFileManagerURL === "undefined") {
    window.tonicsFileManagerURL = window.parent.tonicsFileManagerURL;
}

if (typeof siteURL === "undefined") {
    window.siteURL = window.parent.siteURL;
}

if (typeof siteTimeZone === "undefined") {
    window.siteTimeZone = window.parent.siteTimeZone;
}

window.parent.postMessage({
    mceAction: 'execCommand',
    cmd: 'tonics:OpenedFieldSelectionManager',
    value: ''
}, siteURL);

let chooseMenuFields = document.querySelector('.choose-field-button');
let InsertFieldsButton = document.querySelector('.insert-field-button');
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
                    let fieldMenuUl = document.querySelector('.field-menu-ul');
                    if (fieldMenuUl) {
                        fieldMenuUl.innerHTML = data.data;
                    }
                }
            });
        }
    });
}

if (InsertFieldsButton) {
    InsertFieldsButton.addEventListener('click', (e) => {
        let collateFieldObj = new CollateFieldItemsOnFieldsEditorsSubmit();
        collateFieldObj.fieldSubmitEvObj = new OnSubmitFieldEditorsFormEvent();

        let url = window.location.href + "?action=wrapCollatedFieldItems";
        let defaultHeader = {
            'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`,
             action: 'wrapCollatedFieldItems',
        };
        new XHRApi(defaultHeader).Post(url, JSON.stringify(collateFieldObj.setListDataArray()), function (err, data) {
            if (data) {
                data = JSON.parse(data);
                window.parent.postMessage({
                    mceAction: 'execCommand',
                    cmd: 'tonics:FieldSelectedData',
                    value: data.data
                }, siteURL);
            }
        });
    });
}

window.addEventListener('message', (e) => {
    var data = e.data;
    if (e.origin !== siteURL) {
        return;
    }
    if (data.type === 'tonics:FieldSelectedData' && data.message !== null){
        let message = data.message;
        let url = window.location.href + "?action=unwrapCollatedFieldItems";
        let defaultHeader = {
            'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`,
            action: 'unwrapCollatedFieldItems',
        };
        new XHRApi(defaultHeader).Post(url, message, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                let fieldMenuUl = document.querySelector('.field-menu-ul');
                if (fieldMenuUl) {
                    fieldMenuUl.innerHTML = data.data;
                }
            }
        });
    }
});

if (parent.tinymce && parent.tinymce.activeEditor) {
    window.tinymce = parent.tinymce;
    try {
        let tinyEntryContent = tinymce.activeEditor.dom.select('.entry-content')[0];
        let tinyMenuToggle = new MenuToggle('.menu-arranger', new Query().setQueryResult(tinyEntryContent));
        tinyMenuToggle.setQueryResult(tinyEntryContent);
        tinyMenuToggle
            .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
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


