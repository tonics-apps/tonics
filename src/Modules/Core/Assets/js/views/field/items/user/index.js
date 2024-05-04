
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

let scripts = document.querySelectorAll("[data-script_path]");
scripts.forEach((script) => {
    loadScriptDynamically(script.dataset.script_path, script.dataset.script_path).then()
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
            let url = "/admin/tools/field/get-field-items" + "?action=getFieldItems";
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
   let fieldMenuULFieldSelectionDropperMap = new Map();
    fieldMenuUL.addEventListener('click', (e) => {
        let el = e.target;
        if (el.classList.contains('delete-menu-arrange-item')) {
            let arranger = el.closest('.draggable');
            if (arranger) {
                arranger.remove();
            }
        }

        if (el.classList.contains('tonics-field-selection-dropper-select')) {
            let selectedFieldSlugValue = el.value;
            if (selectedFieldSlugValue){
                let tonicsFieldSelectionDropperUL = el?.closest('.tonics-field-selection-dropper-form-group').querySelector('.tonics-field-selection-dropper-ul');
                let mainFieldSlug = el?.closest('.tonics-field-selection-dropper-form-group').querySelector(`input[name="main_field_slug"]`);
                if (mainFieldSlug.value === selectedFieldSlugValue){
                    fieldMenuULFieldSelectionDropperMap.set(selectedFieldSlugValue, tonicsFieldSelectionDropperUL.cloneNode(true));
                }
            }
        }
    });

    fieldMenuUL.addEventListener('change', (e) => {
        let el = e.target;
        // PERSIST INPUT CHANGES, SHOULD BE REPLACE WITH AN HELPER FUNCTION
        let input = e.target, tagName = input.tagName;
        if (tagName.toLowerCase() === 'input'){
            input.setAttribute('value', input.value);
            if (input.type === 'checkbox'){
                (input.checked) ? input.setAttribute('checked', input.checked) : input.removeAttribute('checked');
            }

            if(input.type === 'radio'){
                let parentRadio = input.parentElement;
                if (parentRadio && parentRadio.querySelectorAll(`input[name="${input.name}"]`).length > 0){
                    parentRadio.querySelectorAll(`input[name="${input.name}"]`).forEach((radio) => {
                        radio.removeAttribute('checked');
                    });
                }
                (input.checked) ? input.setAttribute('checked', input.checked) : input.removeAttribute('checked');
            }
        }
        if (tagName.toLowerCase() === 'textarea'){
            let text = input.value;
            input.innerHTML = text;
            input.value = text;
        }
        if (tagName.toLowerCase() === 'select'){
            input.options[input.selectedIndex].selected = 'selected';
            input.options[input.selectedIndex].setAttribute('selected', 'selected');
        }
        if (el.classList.contains('tonics-field-selection-dropper-select')) {
            let selectedFieldSlug = el.options[el.selectedIndex];
            el.options[el.selectedIndex].selected = 'selected';
            el.options[el.selectedIndex].setAttribute('selected', 'selected');
            if (selectedFieldSlug.value){
                let selectedFieldSlugValue = selectedFieldSlug.value;
                let tonicsFieldSelectionDropperUL = el?.closest('.tonics-field-selection-dropper-form-group').querySelector('.tonics-field-selection-dropper-ul');
                let slug = {
                    action: 'getFieldItems',
                    fieldSlug: JSON.stringify([selectedFieldSlugValue])
                }
                if (fieldMenuULFieldSelectionDropperMap.has(selectedFieldSlugValue)) {
                    tonicsFieldSelectionDropperUL.replaceWith(fieldMenuULFieldSelectionDropperMap.get(selectedFieldSlugValue));
                } else {
                    let url = "/admin/tools/field/get-field-items" + "?action=getFieldItems";
                    new XHRApi({...{}, ...slug}).Get(url, function (err, data) {
                        if (data) {
                            data = JSON.parse(data);
                            if (tonicsFieldSelectionDropperUL){
                                tonicsFieldSelectionDropperUL.innerHTML = data.data;
                            }
                        }
                    });
                }
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