/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

let tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');
if (tonicsFieldSaveChangesButton) {
    tonicsFieldSaveChangesButton.addEventListener('click', (e) => {
        e.preventDefault();
        let eventDispatcher = window.TonicsEvent.EventDispatcher;
        let OnSubmitFieldEditorsForm = new OnSubmitFieldEditorsFormEvent(e);
        eventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnSubmitFieldEditorsForm, OnSubmitFieldEditorsFormEvent);
        let fieldsEditorsForm = document.getElementById('EditorsForm');
        fieldsEditorsForm.submit();
    });
}

class OnSubmitFieldEditorsFormEvent {

    editorsForm = null;

    constructor(e) {
        this.editorsForm = document.getElementById('EditorsForm');
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