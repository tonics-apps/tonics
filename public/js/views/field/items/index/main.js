import * as myModule from "./script-combined.js";
window.myModule = myModule;

// Load Fields Scripts:
let scripts = document.querySelectorAll("[data-script_path]");
scripts.forEach((script) => {
    myModule.loadScriptDynamically(script.dataset.script_path, script.dataset.script_path).then()
});

let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    fieldChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    fieldFormCollected = new Map();

let menuArranger = document.getElementsByClassName('menu-arranger')[0];
let fieldPickerContainer = document.getElementsByClassName('menu-field')[0];

let fieldSlug = document.querySelector('input[name="field_slug"]'),
    fieldID = document.querySelector('input[name="field_id"]');
if (fieldSlug){
    fieldSlug = fieldSlug.value
}
if (fieldID){
    fieldID = fieldID.value
}

try {
    let menuField = document.querySelector('.menu-field');
    if (menuField){
        new myModule.MenuToggle('.menu-field', new myModule.Query())
            .settings('.menu-box-li', '.dropdown-toggle', '.child-menu')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();
    }

    if (menuArranger){
        new myModule.MenuToggle('.menu-arranger', new myModule.Query())
            .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .closeOnClickOutSide(false)
            .stopPropagation(false)
            .run();
    }
} catch (e) {
    console.log("Can't set MenuToggle: menu-widget or menu-arranger");
}

new myModule.Draggables(parent)
    .settings(fieldChild, ['legend', 'input', 'textarea', 'select', 'label'], false) // draggable element
    .onDragDrop(function (element, self) {
        // to the right
        let elementDragged = self.getDragging().closest(fieldChild);

        let dragToTheBottom = document.querySelector(parent).querySelector('.drag-to-the-bottom');
        if (bottom && dragToTheBottom) {
            swapNodes(elementDragged, dragToTheBottom, self.draggingOriginalRect);
            dragToTheBottom.classList.remove('drag-to-the-bottom', 'drag-to-the-top', 'nested-to-the-left', 'nested-to-the-right');
            bottom = false;
        }

        let dragToTheTop = document.querySelector(parent).querySelector('.drag-to-the-top');
        if (top && dragToTheTop){
            swapNodes(elementDragged, dragToTheTop, self.draggingOriginalRect);
            dragToTheTop.classList.remove('drag-to-the-bottom', 'drag-to-the-top', 'nested-to-the-left', 'nested-to-the-right');
            top = false;
        }
        setListDataArray();
    }).onDragTop((element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheTop = element.previousElementSibling;
        if (dragToTheTop && dragToTheTop.classList.contains('menu-arranger-li')){
            top = true;
            dragToTheTop.classList.add('drag-to-the-top');
        }
        sensitivity = 0;
    }
}).onDragBottom( (element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheBottom = element.nextElementSibling;
        if (dragToTheBottom && dragToTheBottom.classList.contains('menu-arranger-li')) {
            bottom = true;
            dragToTheBottom.classList.add('drag-to-the-bottom');
        }
        sensitivity = 0;
    }
}).run();

function setListDataArray() {
    if(draggable){
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            let id = i + 1;
            draggable[i].setAttribute("data-id", id); // add ID's to all draggable item
            let parentID = null;
            let parentDraggable = draggable[i].parentElement.closest('.draggable');
            if (parentDraggable){
                parentID = parentDraggable.getAttribute("data-id");
            }
            draggable[i].setAttribute("data-parentid",
                (draggable[i].classList.contains('menu-arranger-li'))  ? parentID : null)
        }
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            let cell = 1;
            let cellsEl = draggable[i].querySelectorAll('.row-col-item');
            cellsEl.forEach((cellEl) => {
                if (cellEl.querySelector('.draggable')){
                    if (cellEl.querySelector('.draggable').dataset.parentid === draggable[i].dataset.id){
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
            fieldName = '',
            fieldSettingsEl = document.querySelectorAll('.widgetSettings'),
            i = 0,
            parentID = null;
        fieldSettingsEl.forEach(form => {
            let formTagname = form.tagName.toLowerCase();
            if (formTagname === 'form' || formTagname === 'div'){
                let draggable = form.closest('.draggable');
                parentID = draggable.getAttribute('data-parentid');
                if (parentID === 'null'){
                    parentID = null;
                }
                if(draggable.querySelector('input[name="field_slug"]') ){
                    fieldName = draggable.querySelector('input[name="field_slug"]').value;
                }
                let elements = form.querySelectorAll('input, textarea, select'),
                    firstElementParentID = elements[0].closest('.draggable').getAttribute('data-id');

                let widgetSettings = {};
                let collectCheckboxes = draggable.querySelectorAll("[data-collect_checkboxes]");
                collectCheckboxes.forEach((checkbox) => {
                    let checkboxName = checkbox.name;
                    if (!widgetSettings.hasOwnProperty(checkboxName)){
                        widgetSettings[checkboxName] = [];
                    }
                    if (checkbox.checked){
                        widgetSettings[checkboxName].push(checkbox.value);
                    }
                });

                elements.forEach((inputs) => {
                    if (inputs.closest('.draggable').dataset.id === firstElementParentID){
                        if (!widgetSettings.hasOwnProperty(inputs.name)){
                            widgetSettings[inputs.name] = inputs.value;
                            if (draggable.closest("[data-cell]")){
                                widgetSettings[`${fieldName}_cell`] = draggable.closest("[data-cell]").dataset.cell;
                            }
                        }
                    }
                });
                i = i+1;
                ListArray.push({
                    "fk_field_id": fieldID,
                    "field_id": i,
                    "field_parent_id": (draggable.classList.contains('menu-arranger-li')) ? parentID : null,
                    "field_name": fieldName,
                    "field_options": JSON.stringify(widgetSettings),
                });
            }
        });
        return ListArray;
    }
}

/**
 * @param requestHeaders
 * @protected
 */
function defaultXHR(requestHeaders = {})
{
    let defaultHeader = {};
    return new XHRApi({...defaultHeader, ...requestHeaders});
}

if(fieldPickerContainer){
    fieldPickerContainer.addEventListener('click',  (e) => {
        let el = e.target
        if(el.classList.contains('is-menu-checked')) {
            let checkedItems = el.parentNode.querySelectorAll('input[name=field-item]:checked');
            if (checkedItems.length > 0){
                checkedItems.forEach(((checkbox, key) => {
                    if (checkbox.dataset.hasOwnProperty('script_path')) {
                        myModule.loadScriptDynamically(checkbox.dataset.script_path, checkbox.dataset.script_path).then((e) => {
                            fieldSelectedHandler(checkbox);
                        });
                    } else {
                        fieldSelectedHandler(checkbox);
                    }
                }));
            }
        }
    });
}

function fieldSelectedHandler(checkbox) {
    let selectedCellItems = document.querySelectorAll('input[name=cell]:checked');
    checkbox.checked = false;
    let action = checkbox.dataset.action,
        name = checkbox.dataset.name,
        slug = checkbox.dataset.slug,
        url = window.location.href + `?action=${action}&slug=${slug}`;

    let form = '';
    if (fieldFormCollected.has(slug)) {
        form = fieldFormCollected.get(slug);
        if (selectedCellItems.length > 0){
            selectedCellItems.forEach(cell => {
                cell = cell.closest('.row-col-item');
                cell.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
            });
        } else {
            if (menuArranger) {
                menuArranger.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
            }
        }
    } else {
        defaultXHR().Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (data.hasOwnProperty('status') && data.status === 200) {
                    fieldFormCollected.set(slug, data.data);
                    form = fieldFormCollected.get(slug);
                    if (selectedCellItems.length > 0){
                        selectedCellItems.forEach(cell => {
                            cell = cell.closest('.row-col-item');
                            cell.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
                        });
                    } else {
                        if (menuArranger) {
                            menuArranger.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
                        }
                    }
                }
            }
        });
    }
}

function unSelectSelectedCell() {
    let selectedCellItems = document.querySelectorAll('[data-selected="true"]');
    selectedCellItems.forEach(cell => {
        unHighlightFile(cell);
    });
}

function generateFieldData(name, slug, more) {
    let changeID = (Math.random()*1e32).toString(36);
    more = more.replace(/CHANGEID/gi, changeID);
    return `<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <fieldset
            class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center flex-d:column owl">
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">${name}</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <div role="form" data-widget-form="true" class="widgetSettings d:none flex-d:column menu-widget-information cursor:pointer owl width:100% margin-top:0">
                <input type="hidden" name="field_slug" value="${slug}">
                <input type="hidden" name="field_slug_unique_hash" value="${changeID}">
                ${more}
                <div class="form-group">
                    <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete Field Item
                    </button>
                </div>
            </div>
        </fieldset>
    </li>`
}

// delete menu or widget
if (menuArranger){
    menuArranger.addEventListener('click', (e) => {
        let el = e.target;
        if (el.classList.contains('delete-menu-arrange-item')){
            let arranger = el.closest('.draggable');
            if (arranger){
                arranger.remove();
                setListDataArray();
            }
        }
    });
}


// save menu builder
let saveAllMenu = document.querySelector('.save-menu-builder-changes'),
    saveMenuChangesForm = document.getElementById('saveFieldBuilderItems');
if(saveAllMenu && saveMenuChangesForm){
    saveAllMenu.addEventListener('click', function (e) {
        e.preventDefault();
        setListDataArray();
        addHiddenInputToForm(saveMenuChangesForm, 'fieldSlug', fieldSlug);
        addHiddenInputToForm(saveMenuChangesForm, 'fieldDetails', JSON.stringify({
            fieldID: fieldID, // This is the field_slug that houses the menu items
            fieldSlug: fieldSlug, // This is the field_slug that houses the menu items
            fieldItems: getListDataArray(),
        }));
        saveMenuChangesForm.submit();
    })
}
