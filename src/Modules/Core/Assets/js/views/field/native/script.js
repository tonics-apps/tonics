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

if (typeof menuArranger === 'undefined') {
    var menuArranger = document.querySelector('.menu-arranger');
}

if (typeof tonicsFieldSaveChangesButton === 'undefined') {
    var tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');
}

let repeaterParent = '.menu-arranger',
    repeaterDraggable, repeaterChild = '[data-slug="modular_rowcolumnrepeater"]';
if ((repeaterDraggable = window?.TonicsScript?.Draggables)) {
    let repeaterTop = false, repeaterBottom = false,
        repeaterSensitivity = 0, repeaterSensitivityMax = 5;
    repeaterDraggable(repeaterParent).settings(repeaterChild, [
        'input', 'textarea', 'select', 'label', 'button',
        'legend', 'svg', 'span', 'pre', 'code', 'p', '[data-draggable-ignore]',
        '.tox-tinymce', '.tox-statusbar', '.tox-editor-header', 'iframe'
    ], false)
        .onDragDrop((element, self) => {
            let elementDropped = self.getDroppedTarget()?.closest(repeaterChild);
            let elementDragged = self.getDragging()?.closest(repeaterChild);
            if (elementDropped && elementDropped !== elementDragged) {
                // swap element
                let swapNodes;
                if ((swapNodes = window?.TonicsScript?.swapNodes)) {
                    let rowColElDragged = elementDragged.querySelector('.repeater-field');
                    let rowColElDropped = elementDropped.querySelector('.repeater-field');
                    if ((rowColElDragged && rowColElDropped) && rowColElDragged.dataset.repeater_field_name === rowColElDropped.dataset.repeater_field_name) {
                        swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
                        repeaterSensitivity = 0;
                        repeaterTop = false;
                        repeaterBottom = false;
                    }
                }
            }

        }).run();
}

let selectionManager = document.querySelector('.selection-manager');
if (selectionManager) {
    new MenuToggle('.selection-manager', new Query())
        .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["d:none"], ["d:flex"])
        .menuIsOn(["d:flex"], ["d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();
}

class CollateFieldItemsOnFieldsEditorsSubmit {
    draggable = null;
    editorsForm = null;
    /** @type OnSubmitFieldEditorsFormEvent */
    fieldSubmitEvObj = null;

    constructor(event = null, parentEl = document) {
        this.handle(event, parentEl);
    }

    handle(event = null, parentEl = document) {
        this.draggable = parentEl.querySelectorAll('.field-builder-items');
        if (event) {
            this.fieldSubmitEvObj = event;
            if (event.editorsForm && parentEl === document) {
                event.addHiddenInputToForm(event.editorsForm, '_fieldDetails', JSON.stringify(this.setListDataArray()));
            }
        }
    }

    setListDataArray(parentEl = document) {
        if (this.draggable) {
            let draggable = this.draggable;
            for (let i = 0, len = draggable.length; i < len; i++) {
                let id = i + 1;
                draggable[i].setAttribute("data-id", id); // add ID's to all draggable item
                let parentID = null;
                let parentDraggable = draggable[i].parentElement.closest('.field-builder-items');
                if (parentDraggable) {
                    parentID = parentDraggable.getAttribute("data-id");
                }
                draggable[i].setAttribute("data-parentid",
                    (draggable[i].classList.contains('field-builder-items')) ? parentID : null)
            }
            for (let i = 0, len = draggable.length; i < len; i++) {
                let cell = 1;
                let cellsEl = draggable[i].querySelectorAll('.row-col-item');
                cellsEl.forEach((cellEl) => {
                    if (cellEl.querySelector('.field-builder-items')) {
                        if (cellEl.querySelector('.field-builder-items').dataset.parentid === draggable[i].dataset.id) {
                            cellEl.dataset.cell = `${cell}`;
                            cell++;
                        }
                    }
                });
            }
            return this.getListDataArray(parentEl);
        }
    }

    getListDataArray(parentEl = document) {

        if (this.draggable) {
            let ListArray = [], fieldName = '', fieldMainSlug = '', fieldInputName = '',
                fieldSettingsEl = parentEl.querySelectorAll('.widgetSettings'),
                i = 0, parentID = null, self = this;
            fieldSettingsEl.forEach(form => {
                let formTagname = form.tagName.toLowerCase();
                let toggleState = null;
                if (formTagname === 'form' || formTagname === 'div') {
                    let draggable = form.closest('.field-builder-items');
                    parentID = draggable.getAttribute('data-parentid');
                    if (parentID === 'null') {
                        parentID = null;
                    } else {
                        parentID = parseInt(parentID);
                    }

                    toggleState = draggable.querySelector('button[data-field_toggle]')?.ariaExpanded.toLowerCase();
                    if (toggleState === 'true' || toggleState === 'false') {
                        toggleState = toggleState === 'true';
                    }

                    if (draggable.querySelector('input[name="field_slug"]')) {
                        fieldName = draggable.querySelector('input[name="field_slug"]').value;
                    }

                    if (draggable.querySelector('input[name="main_field_slug"]')) {
                        fieldMainSlug = draggable.querySelector('input[name="main_field_slug"]').value;
                    }

                    if (draggable.querySelector('input[name="field_input_name"]')) {
                        fieldInputName = draggable.querySelector('input[name="field_input_name"]').value;
                    }

                    let elements = form.querySelectorAll('input, textarea, select'),
                        firstElementParentID = elements[0].closest('.field-builder-items').getAttribute('data-id');

                    let fieldSettings = {};
                    for (let i = 0; i < elements.length; i++) {
                        let inputs = elements[i];
                        let inputFieldBuilderItems = inputs.closest('.field-builder-items');
                        if (inputFieldBuilderItems.dataset.id === firstElementParentID) {
                            fieldSettings = self.fieldSubmitEvObj.getInputData(inputs, fieldSettings);
                            fieldSettings['_dataset'] = inputFieldBuilderItems?.dataset;
                            let repeatEl = inputs.closest('.widgetSettings').querySelector('[data-repeater_input_name]');
                            if (fieldName === 'modular_rowcolumnrepeater') {
                                let field = {};
                                field.inputName = repeatEl.dataset.repeater_input_name;
                                let cellPosition = repeatEl.closest('[data-cell_position]');
                                let repeaterButtonsIsNextSiblingEl = repeatEl.closest('[data-slug="modular_rowcolumnrepeater"]').nextElementSibling;
                                let repeaterButtonsIsNextSibling = (repeaterButtonsIsNextSiblingEl) ? repeaterButtonsIsNextSiblingEl.classList.contains('row-col-repeater-button') : false;
                                if (cellPosition) {
                                    cellPosition = cellPosition.dataset.cell_position;
                                } else {
                                    cellPosition = null;
                                }

                                field.field_slug_unique_hash = inputs.closest('.widgetSettings').querySelector('input[name="field_slug_unique_hash"]').value;
                                field.field_slug = inputs.closest('.widgetSettings').querySelector('input[name="field_slug"]').value;
                                field.field_name = repeatEl.dataset.repeater_field_name;
                                field.depth = repeatEl.dataset.repeater_depth;
                                field.repeat_button_text = repeatEl.dataset.repeater_repeat_button_text;
                                field.grid_template_col = repeatEl.dataset.grid_template_col;
                                field.row = repeatEl.dataset.row;
                                field.column = repeatEl.dataset.col;
                                field._cell_position = cellPosition;
                                field._can_have_repeater_button = repeaterButtonsIsNextSibling;

                                fieldSettings['_moreOptions'] = field;
                            }
                            fieldSettings['_cell_position'] = elements[i].closest('[data-cell_position]')?.dataset.cell_position;
                            fieldSettings['toggle_state'] = toggleState;
                        }
                    }

                    i = i + 1;
                    ListArray.push({
                        "field_id": i,
                        "field_parent_id": (draggable.classList.contains('field-builder-items')) ? parentID : null,
                        "field_name": fieldName,
                        "field_input_name": fieldInputName,
                        "main_field_slug": fieldMainSlug,
                        "field_options": JSON.stringify(fieldSettings)
                    });
                }
            });

            return ListArray;
        }
    }
}

if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnSubmitFieldEditorsFormEvent.push(...[CollateFieldItemsOnFieldsEditorsSubmit]);
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

            if (el.closest('input[type="range"]')) {
                let inputRange = el.closest('input[type="range"]');
                inputRange.nextElementSibling.value = inputRange.value;
            }

            if (el.closest('[name="grid_template_col"]')) {
                let gridTemplateCol = el.closest('[name="grid_template_col"]');
                let rowColParent = el.closest('[name="grid_template_col"]').closest('.row-col-parent'),
                    rowColItemContainer = rowColParent.querySelector('.rowColumnItemContainer');
                rowColItemContainer.style.gridTemplateColumns = `${gridTemplateCol.value}`;
                return;
            }

            if (el.closest('.rowColumn')) {
                let rowCol = el.closest('.rowColumn'), row = rowCol.querySelector("[name='row']").value,
                    column = rowCol.querySelector("[name='column']").value,
                    rowColParent = rowCol.closest('.row-col-parent');
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
                rowColRepeaterButton.insertAdjacentElement('beforebegin', changeRepeaterTabsIDOnClone(cloneFrag));
            }

            let removeRowColRepeaterButton;
            if ((removeRowColRepeaterButton = el.closest('.remove-row-col-repeater-button'))) {
                removeRowColRepeaterButton?.closest('.field-builder-items').remove();
            }

        });
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


function changeRepeaterTabsIDOnClone(cloneFrag) {

    let toCheck = [];

    cloneFrag.querySelectorAll('.tonicsFieldTabsContainer').forEach((container, containerIndex) => {
        // Get all input and label elements within the container
        const inputs = container.querySelectorAll('input[data-row-tabber]');
        const uniqueNameAttr = container.querySelector('input[name="field_slug_unique_hash"]')?.value + '_' + (Math.random() * 1e32).toString(36);

        inputs.forEach((input, inputIndex) => {
            // Store the checked state of the input
            const isChecked = input.checked;

            // Generate unique suffix based on container index and input index
            const uniqueSuffix = `_${containerIndex}_${inputIndex}_${(Math.random() * 1e32).toString(36)}`;

            // Modify the data-unique attribute
            input.setAttribute('data-unique', input.getAttribute('data-unique') + uniqueSuffix);

            // Modify the id attribute of the input
            const newId = input.getAttribute('id') + uniqueSuffix;
            input.setAttribute('id', newId);

            // Modify the name attribute of the input if necessary (optional)
            input.setAttribute('name', uniqueNameAttr);

            // Find the corresponding label and modify its attributes
            const label = container.querySelector(`label[for="${input.getAttribute('id').replace(uniqueSuffix, '')}"]`);
            if (label) {
                // Modify the data-unique attribute
                label.setAttribute('data-unique', label.getAttribute('data-unique') + uniqueSuffix);

                // Modify the for attribute of the label to match the input's id
                label.setAttribute('for', newId);
            }

            // store the checked for later restoration
            // if we do not removeAttribute and checked later once everything is done, it doesn't work
            if (isChecked) {
                input.removeAttribute('checked');
                toCheck.push(input);
            }
        });
    });

    // restore checked
    toCheck.forEach((input) => input.click())
    return cloneFrag;
}