
let menuArranger = document.querySelector('.menu-arranger');
let tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');

let repeaterParent = '.menu-arranger',
    repeaterDraggable, repeaterChild = '[data-slug="modular_rowcolumnrepeater"]';
if ((repeaterDraggable = window?.TonicsScript?.Draggables)){
    let repeaterTop = false, repeaterBottom = false,
        repeaterSensitivity = 0, repeaterSensitivityMax = 5;
    repeaterDraggable(repeaterParent).settings(repeaterChild, ['input', 'textarea', 'select', 'label', 'button', 'legend'], false)
        .onDragDrop((element, self) => {
            let elementDropped = self.getDroppedTarget()?.closest(repeaterChild);
            let elementDragged = self.getDragging()?.closest(repeaterChild);
            if (elementDropped && elementDropped !== elementDragged){
                // swap element
                let swapNodes;
                if ((swapNodes = window?.TonicsScript?.swapNodes)){
                    let rowColElDragged = elementDragged.querySelector('.repeater-field');
                    let rowColElDropped = elementDropped.querySelector('.repeater-field');
                    if ((rowColElDragged && rowColElDropped) && rowColElDragged.dataset.repeater_field_name === rowColElDropped.dataset.repeater_field_name){
                        swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
                        repeaterSensitivity = 0;
                        repeaterTop = false; repeaterBottom = false;
                    }
                }
            }

        }).run();
}

function nativeFieldModules() {

    if (menuArranger) {
        menuArranger.addEventListener('change', (e) => {
            let el = e.target;
            if (el.closest('select[name=dateType]')) {
                let dateSelect = el.closest('select[name=dateType]');
                let dateParent = dateSelect.closest("[data-widget-form='true']");
                let dateMin = dateParent.querySelector("[name='min']");
                let dateMax = dateParent.querySelector("[name='max']");
                if (dateMin) {
                    dateMin.type = dateSelect.value;
                }
                if (dateMax) {
                    dateMax.type = dateSelect.value;
                }
            }

            if (el.closest('[name="grid_template_col"]')){
                let gridTemplateCol = el.closest('[name="grid_template_col"]');
                let rowColParent =  el.closest('[name="grid_template_col"]').closest('.row-col-parent'),
                    rowColItemContainer = rowColParent.querySelector('.rowColumnItemContainer');
                rowColItemContainer.style.gridTemplateColumns = `${gridTemplateCol.value}`;
                return;
            }

            if (el.closest('.rowColumn')) {
                let rowCol = el.closest('.rowColumn'), row = rowCol.querySelector("[name='row']").value,
                    column = rowCol.querySelector("[name='column']").value, rowColParent = rowCol.closest('.row-col-parent');
                updateRowCol(row, column, rowColParent);
            }
        })
        menuArranger.addEventListener('click', (e) => {
            let el = e.target;
            if (el.closest('.row-col-item')) {
                // If there is a nested list item, we prevent further click from this handler,
                // this way things can be less confusing when we have multiple nested items
                if (el.closest('.row-col-item').querySelector('li')) {
                    el.closest('.row-col-item').dataset.prevent_click = 'true'
                } else {
                    el.closest('.row-col-item').dataset.prevent_click = 'false'
                }
                if (el.closest('.row-col-item').dataset.prevent_click === 'false') {
                    // checkbox.checked = false;
                    let rowColItem = el.closest('.row-col-item').querySelector('input[name=cell]');
                    // Toggle Click
                    rowColItem.checked = !rowColItem.checked;
                }
            }

            let rowColRepeaterButton;
            if ((rowColRepeaterButton = el.closest('.row-col-repeater-button'))) {
                let repeaterFragment = rowColRepeaterButton.querySelector('template.repeater-frag');
                let cloneFrag = repeaterFragment?.content?.querySelector('.field-builder-items').cloneNode(true);
                rowColRepeaterButton.insertAdjacentElement('beforebegin', cloneFrag);
            }

            let removeRowColRepeaterButton;
            if ((removeRowColRepeaterButton = el.closest('.remove-row-col-repeater-button'))) {
                removeRowColRepeaterButton?.closest('.field-builder-items').remove();
            }
        });
    }

    if (tonicsFieldSaveChangesButton){
        tonicsFieldSaveChangesButton.addEventListener('click', (e) => {
            e.preventDefault();
            let draggable = document.querySelectorAll('.field-builder-items');
            function setListDataArray() {
                if(draggable){
                    for(let i = 0, len = draggable.length ; i < len ; i++) {
                        let id = i + 1;
                        draggable[i].setAttribute("data-id", id); // add ID's to all draggable item
                        let parentID = null;
                        let parentDraggable = draggable[i].parentElement.closest('.field-builder-items');
                        if (parentDraggable){
                            parentID = parentDraggable.getAttribute("data-id");
                        }
                        draggable[i].setAttribute("data-parentid",
                            (draggable[i].classList.contains('field-builder-items'))  ? parentID : null)
                    }
                    for(let i = 0, len = draggable.length ; i < len ; i++) {
                        let cell = 1;
                        let cellsEl = draggable[i].querySelectorAll('.row-col-item');
                        cellsEl.forEach((cellEl) => {
                            if (cellEl.querySelector('.field-builder-items')){
                                if (cellEl.querySelector('.field-builder-items').dataset.parentid === draggable[i].dataset.id){
                                    cellEl.dataset.cell =`${cell}`;
                                    cell++;
                                }
                            }
                        });
                    }
                    return getListDataArray();
                }
            }
            function getListDataArray() {
                if(draggable){
                    let ListArray = [],
                        fieldName = '', fieldMainSlug = '',
                        fieldSettingsEl = document.querySelectorAll('.widgetSettings'),
                        i = 0,
                        parentID = null;
                    fieldSettingsEl.forEach(form => {
                        let formTagname = form.tagName.toLowerCase();
                        if (formTagname === 'form' || formTagname === 'div'){
                            let draggable = form.closest('.field-builder-items');
                            parentID = draggable.getAttribute('data-parentid');
                            if (parentID === 'null'){
                                parentID = null;
                            } else {
                                parentID = parseInt(parentID);
                            }

                            if(draggable.querySelector('input[name="field_slug"]') ){
                                fieldName = draggable.querySelector('input[name="field_slug"]').value;
                            }

                            if(draggable.querySelector('input[name="main_field_slug"]') ){
                                fieldMainSlug = draggable.querySelector('input[name="main_field_slug"]').value;
                            }

                            let elements = form.querySelectorAll('input, textarea, select'),
                                firstElementParentID = elements[0].closest('.field-builder-items').getAttribute('data-id');

                            let fieldSettings = {};
                            for (let i = 0; i < elements.length; i++) {
                                let inputs = elements[i];
                                if (inputs.closest('.field-builder-items').dataset.id === firstElementParentID){
                                    // collect checkbox
                                    if (inputs.type === 'checkbox'){
                                        let checkboxName = inputs.name;
                                        if (!fieldSettings.hasOwnProperty(checkboxName)){
                                            fieldSettings[checkboxName] = [];
                                        }
                                        if (inputs.checked){
                                            fieldSettings[checkboxName].push(inputs.value);
                                        }
                                    }else if (inputs.type === 'select-multiple'){
                                        let selectOptions = inputs.options;
                                        let selectBoxName = inputs.name;
                                        for (let k = 0; k < selectOptions.length; k++) {
                                            let option = selectOptions[k];
                                            if (option.selected){
                                                if (!fieldSettings.hasOwnProperty(selectBoxName)){
                                                    fieldSettings[selectBoxName] = [];
                                                }

                                                fieldSettings[selectBoxName].push(option.value || option.text);
                                            }
                                        }
                                    }else if (!fieldSettings.hasOwnProperty(inputs.name)) {
                                        fieldSettings[inputs.name] = inputs.value;
                                    }

                                    let repeatEl = inputs.closest('[data-repeater_input_name]');
                                    if (repeatEl){
                                        let field = {};
                                        field.inputName = repeatEl.dataset.repeater_input_name;
                                        let cellPosition = repeatEl.closest('[data-cell_position]');
                                        let repeaterButtonsIsNextSibling = repeatEl.closest('[data-slug="modular_rowcolumnrepeater"]').nextElementSibling;
                                        repeaterButtonsIsNextSibling = (repeaterButtonsIsNextSibling) ? repeaterButtonsIsNextSibling.classList.contains('row-col-repeater-button') : false;
                                        if (cellPosition){
                                            cellPosition = cellPosition.dataset.cell_position;
                                        } else {
                                            cellPosition = null;
                                        }

                                        field.field_slug_unique_hash = repeatEl.closest('.widgetSettings').querySelector('input[name="field_slug_unique_hash"]').value;
                                        field.field_slug = repeatEl.closest('.widgetSettings').querySelector('input[name="field_slug"]').value;
                                        field.field_name = repeatEl.dataset.repeater_field_name;
                                        field.depth = repeatEl.dataset.repeater_depth;
                                        field.repeat_button_text = repeatEl.dataset.repeater_repeat_button_text;
                                        field.grid_template_col = repeatEl.dataset.grid_template_col;
                                        field.row = repeatEl.dataset.row;
                                        field.column = repeatEl.dataset.col;
                                        field._cell_position = cellPosition;
                                        field._can_have_repeater_button = repeaterButtonsIsNextSibling

                                        fieldSettings['_moreOptions'] = field;

                                    }

                                    fieldSettings['_cell_position'] = elements[i].closest('[data-cell_position]')?.dataset.cell_position;
                                }
                            }

                            i = i+1;
                            ListArray.push({
                                "field_id": i,
                                "field_parent_id": (draggable.classList.contains('field-builder-items')) ? parentID : null,
                                "field_name": fieldName,
                                "field_main_slug": fieldMainSlug,
                                "field_options": JSON.stringify(fieldSettings)
                            });
                        }
                    });
                    return ListArray;
                }
            }
            let editorsForm = document.getElementById('EditorsForm');
            function addHiddenInputToForm(form, key, value) {
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
            addHiddenInputToForm(editorsForm, '_fieldDetails', JSON.stringify(setListDataArray()))
             // console.log(repeaters);
             // return;
            editorsForm.submit();
        })

        function getRepeatersData(fieldSettingsEl) {
            let widgetSettings = {},
                fieldBuilderItems = fieldSettingsEl.querySelectorAll('.field-builder-items');

            let fieldSettingsRepeaterName = fieldSettingsEl.dataset.repeater_input_name,  id = 0
            fieldBuilderItems.forEach((fieldList => {
                let elements = fieldList.querySelectorAll('input, textarea, select'),
                    fieldSettings = {};
                for (let i = 0; i < elements.length; i++) {
                    let inputs = elements[i];
                    let inputDataRepeaterDepth = inputs.closest('[data-repeater_input_name]');
                    if (inputDataRepeaterDepth.dataset.repeater_input_name !== fieldSettingsRepeaterName){
                        break;
                    }

                    // collect checkbox
                    if (inputs.type === 'checkbox'){
                        let checkboxName = inputs.name;
                        if (!fieldSettings.hasOwnProperty(checkboxName)){
                            fieldSettings[checkboxName] = [];
                        }
                        if (inputs.checked){
                            fieldSettings[checkboxName].push(inputs.value);
                        }
                    }else if (inputs.type === 'select-multiple'){
                        let selectOptions = inputs.options;
                        let selectBoxName = inputs.name;
                        for (let k = 0; k < selectOptions.length; k++) {
                            let option = selectOptions[k];
                            if (option.selected){
                                if (!fieldSettings.hasOwnProperty(selectBoxName)){
                                    fieldSettings[selectBoxName] = [];
                                }

                                fieldSettings[selectBoxName].push(option.value || option.text);
                            }
                        }
                    }else if (!fieldSettings.hasOwnProperty(inputs.name)) {
                        fieldSettings[inputs.name] = inputs.value;
                    }

                    fieldSettings['_cell_position'] = elements[i].closest('[data-cell_position]')?.dataset.cell_position;
                }

                if (Object.keys(fieldSettings).length !== 0){
                    widgetSettings[id] = fieldSettings;
                    ++id;
                }
            }));

            return widgetSettings;
        }
    }

}

function updateRowCol(row, col, parent) {
    let times = row * col;
    let rowColumnItemContainer = parent.querySelector('.rowColumnItemContainer');
    let rowColItems = rowColumnItemContainer.querySelectorAll(':scope > .row-col-item');

    let cellItems = times - rowColItems.length;

    // if negative
    if (Math.sign(cellItems) === -1) {
        // convert it to positive, and store in toRemove
        let toRemove = -cellItems;
        for (let i = 1; i <= toRemove; i++) {
            rowColumnItemContainer.removeChild(rowColumnItemContainer.lastElementChild);
        }
    }

    // this is non-negative
    if (Math.sign(cellItems) === 1) {
        for (let i = 1; i <= cellItems; i++) {
            rowColumnItemContainer.insertAdjacentHTML('beforeend', getCellForm());
        }
    }
    rowColumnItemContainer.style.setProperty('--row', row);
    rowColumnItemContainer.style.setProperty('--column', col);
}

function getCellForm() {
    let slugID = (Math.random() * 1e32).toString(36);
    return `
<ul style="margin-left: 0;" class="row-col-item">
     <div class="form-group d:flex flex-d:column flex-gap:small">
      <label class="menu-settings-handle-name" for="cell-select-${slugID}">Select & Choose Field
        <input id="cell-select-${slugID}" type="checkbox" name="cell">
      </label>
     </div>
</ul>
`
}

let inputTitle = document.querySelector('[name="post_title"]');
if (!inputTitle) {
    inputTitle = document.querySelector('[name="track_title"]');
}

if (inputTitle) {
    inputTitle.addEventListener('input', (e) => {
        let el = e.target, inputTitle = el.value;
        let seo_title = document.querySelector('[name="seo_title"]'),
            og_title = document.querySelector('[name="og_title"]');
        if (inputTitle) {
            if (seo_title) {
                seo_title.value = (seo_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(seo_title.getAttribute('maxlength')))
                    : inputTitle;
            }
            if (og_title) {
                og_title.value = (og_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(og_title.getAttribute('maxlength')))
                    : inputTitle;
            }
        }
    });
}

nativeFieldModules();