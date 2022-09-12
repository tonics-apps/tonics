let scripts = document.querySelectorAll("[data-script_path]");
scripts.forEach((script) => {
    myModule.loadScriptDynamically(script.dataset.script_path, script.dataset.script_path).then()
});

let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    fieldChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    fieldFormCollected = new Map(),
    fieldMenuUL = document.querySelector('.field-menu-ul');

let fieldSlug = document.querySelector('input[name="field_slug"]'),
    fieldID = document.querySelector('input[name="field_id"]'),
    fieldSelectionContainer = document.querySelector('.field-selection-container');
if (fieldSlug) {
    fieldSlug = fieldSlug.value
}
if (fieldID) {
    fieldID = fieldID.value
}

let menuArrangerLi = document.querySelector('.menu-arranger-li');
if (menuArrangerLi) {
    try {
        const menuToggleUserFieldItems = new MenuToggle('.EditorsForm', new Query());
        menuToggleUserFieldItems
            .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .closeOnClickOutSide(false)
            .stopPropagation(false)
            .run();
    } catch (e) {
        console.log("Can't set MenuToggle: menu-widget or menu-arranger");
    }
}


if (document.querySelector(parent)) {
    // handle dropdown click
    let parentMenuArranger = document.querySelector(parent);
    parentMenuArranger.addEventListener('click', (e) => {
        let el = e.target;
        if (el.closest('.dropdown-toggle') && el.closest(fieldChild)) {
            let dropDown = el.closest('.dropdown-toggle'),
                dropDownBool = dropDown.ariaExpanded === 'false';
            if (dropDownBool) {
                // ${slug}
                let inputFieldSlugUniqueHash = el.closest(fieldChild).querySelector('input[name="field_slug_unique_hash"]');
                let hiddenFieldSlug = el.closest(fieldChild).querySelector(`input[name="hide_field[${inputFieldSlugUniqueHash.value}]"]`);
                if (hiddenFieldSlug) {
                    hiddenFieldSlug.remove();
                }
            } else {
                let inputFieldSlugUniqueHash = el.closest(fieldChild).querySelector('input[name="field_slug_unique_hash"]');
                if (inputFieldSlugUniqueHash) {
                    inputFieldSlugUniqueHash.insertAdjacentHTML('beforebegin', `<input type='hidden' name='hide_field[${inputFieldSlugUniqueHash.value}]' value='${inputFieldSlugUniqueHash.value}'>`)
                }
            }
        }
    });
}

if (fieldSelectionContainer) {
    let fieldContainerButton = fieldSelectionContainer.querySelector('.field-add-button');
    fieldContainerButton.addEventListener('click', (e) => {
        handleFieldSelection();
    });

}

function handleFieldSelection() {
    if (fieldSelectionContainer) {
        let checkedSlug = [];
        let checkedItems = fieldSelectionContainer.querySelectorAll("input[name='field_ids[]']:checked");
        checkedItems.forEach((field) => {
            if (field.dataset.hasOwnProperty('cant_retrieve_field_items') === false) {
                checkedSlug.push(field.value);
            }
        });

        if (checkedSlug.length > 0) {
            let slug = {
                action: 'getFieldItems',
                fieldSlug: JSON.stringify(checkedSlug)
            }
            let url = window.location.href + "?action=getFieldItems";
            new XHRApi({...{}, ...slug}).Get(url, function (err, data) {
                if (data) {
                    data = JSON.parse(data);
                    insertFieldItems(data, checkedSlug);
                }
            });
        }
    }
}

// delete menu or widget
if (fieldMenuUL) {
    fieldMenuUL.addEventListener('click', (e) => {
        let el = e.target;
        if (el.classList.contains('delete-menu-arrange-item')) {
            let arranger = el.closest('.draggable');
            if (arranger) {
                arranger.remove();
            }
        }
    });
}

function insertFieldItems(data, checkedSlug) {
    if (fieldMenuUL) {
        fieldMenuUL.insertAdjacentHTML('beforeend', data.data);
        let draggableElements = fieldMenuUL.querySelectorAll('.draggable');
        checkedSlug.forEach((slug) => {
            draggableElements.forEach((dragEL) => {
                dragEL.insertAdjacentHTML('beforeend', `<input type="hidden" name="field_ids[]" value="${slug}">`);
            })
        });
    }
}