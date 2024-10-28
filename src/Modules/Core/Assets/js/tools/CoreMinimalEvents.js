/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

if (typeof tonicsFieldSaveChangesButton === 'undefined') {
    var tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');
}

if (typeof meniArrangerInCoreMinimal === 'undefined') {
    var meniArrangerInCoreMinimal = document.querySelector('.menu-arranger');
}

if (tonicsFieldSaveChangesButton) {
    tonicsFieldSaveChangesButton.addEventListener('click', (e) => {
        e.preventDefault();

        // Disable the submit button to prevent multiple submissions
        tonicsFieldSaveChangesButton.disabled = true;

        let eventDispatcher = window.TonicsEvent.EventDispatcher;
        let OnSubmitFieldEditorsForm = new OnSubmitFieldEditorsFormEvent(e);
        eventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnSubmitFieldEditorsForm, OnSubmitFieldEditorsFormEvent);
        let fieldsEditorsForm = document.getElementById('EditorsForm');
        fieldsEditorsForm.submit();

        // Re-enable the submit button after a specified timeout (e.g., 5 seconds)
        setTimeout(() => {
            tonicsFieldSaveChangesButton.disabled = false;
        }, 5000); // Timeout in milliseconds (e.g., 5000 ms = 5 seconds)
    });
}

class OnSubmitFieldEditorsFormEvent {

    editorsForm = null;

    constructor(e = null) {
        if (e) {
            this.editorsForm = document.getElementById('EditorsForm');
        }
    }

    addHiddenInputToForm(form, key, value) {
        let inputExist = form.querySelector(`input[name="${key}"]`);
        if (inputExist) {
            inputExist.value = value
        } else {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    }

    getInputData(inputs, settings = {}) {
        const inputName = inputs.name;

        switch (inputs.type) {
            case 'checkbox':
                if (!settings.hasOwnProperty(inputName)) {
                    settings[inputName] = [];
                }
                if (inputs.checked) {
                    settings[inputName].push(inputs.value);
                }
                break;

            case 'select-multiple':
                if (!settings.hasOwnProperty(inputName)) {
                    settings[inputName] = [];
                }
                let selectOptions = inputs.options;
                for (let k = 0; k < selectOptions.length; k++) {
                    let option = selectOptions[k];
                    if (option.selected) {
                        if (!settings.hasOwnProperty(inputName)) {
                            settings[inputName] = [];
                        }
                        settings[inputName].push(option.value || option.text);
                    }
                }
                break;

            case 'select-one': // Handling single select elements
                const selectedOption = inputs.options[inputs.selectedIndex];
                settings[inputName] = selectedOption.value || selectedOption.text;
                break;

            case 'radio':
                if (inputs.checked) {
                    settings[inputName] = inputs.value;
                }
                break;

            default:
                if (!settings.hasOwnProperty(inputName)) {
                    settings[inputName] = inputs.value;
                }
                break;
        }

        return settings;
    }
}


if (meniArrangerInCoreMinimal) {

    window.onload = function () {
        let previews = document.querySelectorAll('[data-field_input_name="tonics-preview-layout"]:checked');
        if (previews.length > 0) {
            previews.forEach(preview => {
                preview.click();
            });
        }
    };

    meniArrangerInCoreMinimal.addEventListener('click', (e) => {
        let el = e.target;
        let tonicsPreviewLayout = el.getAttribute('data-field_input_name') === 'tonics-preview-layout';
        let layout = el.closest('[data-field_input_name="tonics-preview-layout"]');

        if (tonicsPreviewLayout) {
            let tabsField = layout?.closest('.tonicsFieldTabsContainer');
            let builderItems = tabsField?.querySelector('.preview-iframe');
            if (!builderItems) {
                builderItems = tabsField?.querySelector('.field-builder-items');
            }

            if (builderItems) {
                builderItems.classList.remove('field-builder-items');
                builderItems.classList.add('preview-iframe');
                builderItems.innerHTML = '<div class="margin-left:1em loading-animation"></div>';
                let layoutSelector = el.closest('[data-repeater_input_name="layout-selector-modular-repeater"]')?.closest('.field-builder-items');
                if (layoutSelector) {
                    let selectedBreakPoint = layoutSelector.querySelector('select[name="tonics-preview-break-point"]');
                    const ulElement = document.createElement('ul');
                    ulElement.appendChild(layoutSelector.cloneNode(true));
                    let collateFieldItemsObject = new CollateFieldItemsOnFieldsEditorsSubmit(new OnSubmitFieldEditorsFormEvent(null), ulElement);
                    let fieldItems = collateFieldItemsObject.setListDataArray(ulElement);
                    fieldPreviewFromPostData
                    (fieldItems,
                        (data) => {
                            if (data?.data) {
                                const iframe = document.createElement('iframe');
                                builderItems.innerHTML = '';
                                builderItems.appendChild(iframe);
                                iframe.srcdoc = data.data;
                                Object.assign(iframe.style, {
                                    width: selectedBreakPoint?.value || '100%',
                                    height: "100%",
                                    border: "2px dashed rgb(110 102 97)",
                                    resize: "horizontal"
                                });
                                builderItems.style.height = "750px";
                            }
                        }, () => {

                        }, (postData) => {
                            return {layoutSelector: postData};
                        });
                }
            }

        }

    });

}