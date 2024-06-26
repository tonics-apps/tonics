
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

let adminSelectUtilities = document.querySelector('[data-admin-select-utilities="true"]'),
    selectUtilitiesForm = document.getElementById('selectUtilitiesForm');

function removeToken() {
    if (selectUtilitiesForm.querySelector('input[name="token"]')){
        selectUtilitiesForm.querySelector('input[name="token"]').remove();
    }
}
function selectedReloadPage(e, selectedOption){
    if (selectUtilitiesForm){
        removeToken();
        if (selectedOption.dataset.hasOwnProperty('form_action') && selectedOption.dataset.hasOwnProperty('form_method')){
            selectUtilitiesForm.action = selectedOption.dataset.form_action;
            selectUtilitiesForm.method = selectedOption.dataset.form_method;
            selectUtilitiesForm.submit();
        }
    }
}

function selectedTrash(e, selectedOption) {
    let toTrash = getAllSelectedFiles();
    if (selectUtilitiesForm && toTrash.length > 0){
        promptToast("Do you want to Trash Item(s)?", "Trash Item(s)", () => {
            if (selectedOption.dataset.hasOwnProperty('form_action') && selectedOption.dataset.hasOwnProperty('form_method')){
                toTrash.forEach(((value, key) => {
                    addHiddenInputToForm(selectUtilitiesForm, 'itemsToTrash[]', JSON.stringify(value.dataset))
                }))
                selectUtilitiesForm.action = selectedOption.dataset.form_action;
                selectUtilitiesForm.method = selectedOption.dataset.form_method;
                selectUtilitiesForm.submit();
            }
        });
    }
}

function selectedEdit(e, selectedOption)
{
    let editLinksToOpenInNewTab = getAllSelectedFiles();
    if (editLinksToOpenInNewTab){
        editLinksToOpenInNewTab.forEach(((value, key) => {
            if (value.dataset.hasOwnProperty('db_click_link') && value.dataset.db_click_link.length > 1 ){
                window.open(value.dataset.db_click_link, value.dataset.db_click_link);
            }
        }));
    }
}

function selectCTRLKey(e, selectedOption)
{
    if (document.querySelector('[data-simulate_shift_key="true"]')){
        document.querySelector('[data-simulate_shift_key="true"]').dataset.simulate_shift_key = 'false';
    }

    if (selectedOption.dataset.hasOwnProperty('simulate_ctrl_key')){
        selectedOption.dataset.simulate_ctrl_key = 'true';
    }
}

function selectSHIFTKey(e, selectedOption)
{
    if (document.querySelector('[data-simulate_ctrl_key="true"]')){
        document.querySelector('[data-simulate_ctrl_key="true"]').dataset.simulate_ctrl_key = 'false';
    }

    if (selectedOption.dataset.hasOwnProperty('simulate_shift_key')){
        selectedOption.dataset.simulate_shift_key = 'true';
    }
}

function selectDelete(e, selectedOption) {
    let toTrash = getAllSelectedFiles();
    if (selectUtilitiesForm && toTrash.length > 0){
        promptToast("Do you want to Delete Item(s)?", "Delete Item(s)", () => {
            if (selectedOption.dataset.hasOwnProperty('form_action') && selectedOption.dataset.hasOwnProperty('form_method')){
                toTrash.forEach(((value, key) => {
                    addHiddenInputToForm(selectUtilitiesForm, 'itemsToDelete[]', JSON.stringify(value.dataset))
                }))
                selectUtilitiesForm.action = selectedOption.dataset.form_action;
                selectUtilitiesForm.method = selectedOption.dataset.form_method;
                selectUtilitiesForm.submit();
            }
        });
    }
}

function viewAll(e, selectedOption) {
    if (selectUtilitiesForm){
        selectUtilitiesForm.action = selectedOption.dataset.form_action;
        selectUtilitiesForm.method = selectedOption.dataset.form_method;
        removeToken();
        selectUtilitiesForm.submit();
    }
}

function viewTrash(e, selectedOption) {
    if (selectUtilitiesForm){
        selectUtilitiesForm.action = selectedOption.dataset.form_action;
        selectUtilitiesForm.method = selectedOption.dataset.form_method;
        removeToken();
        selectUtilitiesForm.submit();
    }
}

function viewDraft(e, selectedOption) {
    if (selectUtilitiesForm){
        selectUtilitiesForm.action = selectedOption.dataset.form_action;
        selectUtilitiesForm.method = selectedOption.dataset.form_method;
        removeToken();
        selectUtilitiesForm.submit();
    }
}

if (adminSelectUtilities){
    adminSelectUtilities.addEventListener('change', (e) => {
        let el = e.target;
        let selectedOption = el.querySelector(`option[value="${el.value}"]`);
        switch (el.value) {
            case 'reload':
                selectedReloadPage(e, selectedOption);
                break;
            case 'edit':
                selectedEdit(e, selectedOption);
                break;
            case 'trash':
                selectedTrash(e, selectedOption);
                break;
            case 'ctrl':
                selectCTRLKey(e, selectedOption)
                break;
            case 'shift':
                selectSHIFTKey(e, selectedOption)
                break;
            case 'delete':
                selectDelete(e, selectedOption)
                break;
            case 'viewAll':
                viewAll(e, selectedOption)
                break;
            case 'viewTrash':
                viewTrash(e, selectedOption)
                break;
            case 'viewDraft':
                viewDraft(e, selectedOption)
                break;
        }
        el.value = '-1';
    })
}