
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
        if (e){
            this.editorsForm = document.getElementById('EditorsForm');
        }
    }

    addHiddenInputToForm(form, key, value) {
        let inputExist = form.querySelector(`input[name="${key}"]`);
        if (inputExist){
            inputExist.value = value
        }else {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    }

    getInputData(inputs, settings = {}) {
        // collect checkbox
        if (inputs.type === 'checkbox'){
            let checkboxName = inputs.name;
            if (!settings.hasOwnProperty(checkboxName)){
                settings[checkboxName] = [];
            }
            if (inputs.checked){
                settings[checkboxName].push(inputs.value);
            }
        }else if (inputs.type === 'select-multiple'){
            let selectOptions = inputs.options;
            let selectBoxName = inputs.name;
            for (let k = 0; k < selectOptions.length; k++) {
                let option = selectOptions[k];
                if (option.selected){
                    if (!settings.hasOwnProperty(selectBoxName)){
                        settings[selectBoxName] = [];
                    }

                    settings[selectBoxName].push(option.value || option.text);
                }
            }
        }else if (!settings.hasOwnProperty(inputs.name)) {
            settings[inputs.name] = inputs.value;
        }

        return settings;
    }

}