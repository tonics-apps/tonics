
let menuArranger = document.querySelector('.menu-arranger');
let tonicsFieldSaveChangesButton = document.querySelector('.tonics-save-changes');

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
            let formData = new FormData(editorsForm);
            e.preventDefault();

            let tree = {}, root = {}, lastObject = {}, breakLoopBackward = false, childStack = [];
            let repeatersDepth = document.querySelectorAll('[data-repeater_depth]');


            tree._data = [];
            repeatersDepth.forEach((repeatEl => {
                let data = getRepeatersData(repeatEl);
                data._name = repeatEl.dataset.repeater_input_name;
                data._depth = repeatEl.dataset.repeater_depth;
                let currentDepth = parseInt(data._depth);

                if (tree._data.length === 0 || currentDepth === 0){
                    tree._data.push(data);
                    lastObject = data;
                    childStack.push(data);
                    root = tree;
                } else {
                    let lastDepth = parseInt(lastObject._depth);
                    if (currentDepth > lastDepth){
                        if (!lastObject.hasOwnProperty('_data')){
                            lastObject._data = [];
                            lastObject._data.push(data);
                            lastObject = data;
                            childStack.push(data);
                        }
                    }

                    if (currentDepth === lastDepth || currentDepth < lastDepth){
                        for (const treeData of loopTreeBackward(childStack)) {
                            if (treeData._depth < currentDepth){
                                breakLoopBackward = true;
                                treeData._data.push(data);
                                lastObject = data;
                                childStack.push(data);
                            }
                        }
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

           console.log(tree);
        })
    }

    function getRepeatersData(fieldSettingsEl) {
        let widgetSettings = [],
            fieldBuilderItems = fieldSettingsEl.querySelectorAll('.field-builder-items');

        let fieldSettingsRepeaterName = fieldSettingsEl.dataset.repeater_input_name;
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
                 widgetSettings.push(fieldSettings);
             }
         }));

        return widgetSettings;
    }

}

function updateRowCol(row, col, parent) {
    let times = row * col;
    let rowColumnItemContainer = parent.querySelector('.rowColumnItemContainer');
    let rowColItems = parent.querySelectorAll('.rowColumnItemContainer > .row-col-item');
    let cellItems = times - rowColItems.length;
    if (Math.sign(cellItems) === -1) {
        // convert it to positive, and store in toRemove
        let toRemove = -cellItems;
        for (let i = 1; i <= toRemove; i++) {
            rowColumnItemContainer.removeChild(rowColumnItemContainer.lastElementChild);
        }
    }

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