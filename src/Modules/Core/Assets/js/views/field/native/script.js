
let menuArranger = document.querySelector('.menu-arranger');
let tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');

let repeaterParent = '.menu-arranger',
    repeaterDraggable, repeaterChild = '.draggable-repeater';
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
                    if (elementDragged.dataset.repeater_field_name === elementDropped.dataset.repeater_field_name){
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
                let cloneFrag = repeaterFragment?.content?.querySelector('.row-col-parent').cloneNode(true);
                rowColRepeaterButton.insertAdjacentElement('beforebegin', cloneFrag);
            }

            let removeRowColRepeaterButton;
            if ((removeRowColRepeaterButton = el.closest('.remove-row-col-repeater-button'))) {
                removeRowColRepeaterButton?.closest('.row-col-parent').remove();
            }
        });
    }

    if (tonicsFieldSaveChangesButton){
        tonicsFieldSaveChangesButton.addEventListener('click', (e) => {
            let editorsForm = document.getElementById('EditorsForm');
            e.preventDefault();

            let tree = {}, lastObject = {}, breakLoopBackward = false, childStack = [], parent = {};
            let repeatersDepth = document.querySelectorAll('[data-repeater_depth]');

            let firstRepeaterName = document.querySelector('[data-repeater_depth="0"]')?.dataset?.repeater_input_name;

            tree._data = {}; let parentID = 0, childID = -1, fieldItemID = -1, treeTimes = {}, lastDepth = 0;
            repeatersDepth.forEach((repeatEl => {
                let data = getRepeatersData(repeatEl);
                data._configuration = {};

                data._configuration._name = repeatEl.dataset.repeater_input_name;
                data._configuration._field_slug_unique_hash = repeatEl.closest('.widgetSettings').querySelector('input[name="field_slug_unique_hash"]').value;
                data._configuration._field_name = repeatEl.dataset.repeater_field_name;
                data._configuration._depth = repeatEl.dataset.repeater_depth;
                data._configuration._repeat_button_text = repeatEl.dataset.repeater_repeat_button_text;
                data._configuration._grid_template_col = repeatEl.dataset.grid_template_col;

                let currentDepth = parseInt(data._configuration._depth);
                ++childID;

                if (currentDepth === 0){
                    tree._data[parentID] = data;
                    treeTimes[parentID] = {};
                    lastObject = data;
                    lastDepth = parseInt(lastObject._configuration._depth);
                    childStack.push(data);
                    ++parentID;
                } else {
                    lastDepth = parseInt(lastObject._configuration._depth);
                    if (currentDepth > lastDepth){
                        if (!lastObject.hasOwnProperty('_children')){
                            parent = lastObject;
                            lastObject._children = {};
                            lastObject._children[childID] = data;
                            lastObject = data;
                            childStack.push(data);
                        }
                    }

                    if (currentDepth === lastDepth || currentDepth < lastDepth){
                        for (const treeData of loopTreeBackward(childStack)) {
                            if (treeData._configuration._depth < currentDepth){
                                breakLoopBackward = true;
                                treeData._children[childID] = data;
                                lastObject = data;
                                childStack.push(data);
                            }
                        }
                    }
                }


                let fieldTimesParentID = treeTimes[parentID -1];
                if (!fieldTimesParentID.hasOwnProperty(data._configuration._field_name)){
                    ++fieldItemID;
                    fieldTimesParentID[data._configuration._field_name] = {};
                    fieldTimesParentID[data._configuration._field_name]['data'] = {};
                    fieldTimesParentID[data._configuration._field_name]['hash'] = {};
                }

                if (lastDepth > currentDepth){
                    ++fieldItemID;
                }

                if (!fieldTimesParentID[data._configuration._field_name]['data'].hasOwnProperty(fieldItemID)){
                    fieldTimesParentID[data._configuration._field_name]['data'][fieldItemID] = [];
                }

                if (!fieldTimesParentID[data._configuration._field_name]['hash'].hasOwnProperty(fieldItemID)){
                    fieldTimesParentID[data._configuration._field_name]['hash'][fieldItemID] = [];
                }

                fieldTimesParentID[data._configuration._field_name]['data'][fieldItemID].push(data);

                for (const it in data){
                    if (data[it].hasOwnProperty('field_slug_unique_hash')){
                        if (!fieldTimesParentID[data._configuration._field_name]['hash'][fieldItemID].hasOwnProperty(data[it].field_slug_unique_hash)){
                            fieldTimesParentID[data._configuration._field_name]['hash'][fieldItemID][data[it].field_slug_unique_hash] = [];
                        }
                        fieldTimesParentID[data._configuration._field_name]['hash'][fieldItemID][data[it].field_slug_unique_hash].push(data[it]);
                    }
                }

            }));

            function *loopTreeBackward(treeToLoop = null) {
                if (treeToLoop === null){
                    treeToLoop = this.tocTree;
                }
                for (let i = treeToLoop.length - 1; i >= 0; i--){
                    if (breakLoopBackward){break;}
                    yield treeToLoop[i];
                }

                breakLoopBackward = false;
            }

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

            if (firstRepeaterName){
                addHiddenInputToForm(editorsForm, firstRepeaterName, JSON.stringify({'tree': tree, 'treeTimes': treeTimes}));
            }
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
                    }

                    if (!fieldSettings.hasOwnProperty(inputs.name)) {
                        fieldSettings[inputs.name] = inputs.value;
                    }
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

    // this is non negative
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