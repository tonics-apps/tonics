import * as myModule from "./script-combined.js";

let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    widgetChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    menuWidgetSlug = document.querySelector('input[name="widget_slug"]').value,
    menuWidgetID = document.querySelector('input[name="widget_id"]').value,
    widgetFormCollected = new Map();

try {
    new myModule.MenuToggle('.menu-widget', new myModule.Query())
        .settings('.menu-box-li', '.dropdown-toggle', '.child-menu')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .stopPropagation(false)
        .closeOnClickOutSide(false)
        .run();

    new myModule.MenuToggle('.menu-arranger', new myModule.Query())
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

new myModule.Draggables(parent)
    .settings(widgetChild, ['.menu-widget-information', 'legend'], false) // draggable element
    .onDragDrop(function (element, self) {
        let elementDropped = element.closest(widgetChild);
        let elementDragged = self.getDragging().closest(widgetChild);
        if (elementDropped !== elementDragged && top || bottom){
            // swap element
            swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
            sensitivity = 0;
            top = false; bottom = false;
            setListDataArray();
        }
    }).onDragTop((element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheTop = element.previousElementSibling;
        if (dragToTheTop && dragToTheTop.classList.contains('menu-arranger-li')){
            top = true;
        }
    }
}).onDragBottom( (element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheBottom = element.nextElementSibling;
        if (dragToTheBottom && dragToTheBottom.classList.contains('menu-arranger-li')) {
            bottom = true;
        }
    }
}).run();

function setListDataArray() {
    if(draggable){
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            draggable[i].setAttribute("data-id", i + 1); // add ID's to all draggable item
        }
        return getListDataArray();
    }
}

function getListDataArray() {
    if(draggable){
        let ListArray = [],
            widgetName = '',
            widgetSettingsEl = document.querySelectorAll('.widgetSettings'),
            i = 0;
        widgetSettingsEl.forEach(form => {
            if (form.tagName === 'FORM'){
                let widgetSettings = {};
                let widgetFormData = new FormData(form);
                widgetFormData.forEach((value, key) => {
                    widgetSettings[key] = value;
                });

                let draggable = form.closest('.draggable');
                if(draggable.querySelector('input[name="widget_slug"]') ){
                    widgetName = draggable.querySelector('input[name="widget_slug"]').value;
                }
                i = i+1;
                ListArray.push({
                    "fk_widget_id": menuWidgetID,
                    "wgt_id": i,
                    "wgt_name": widgetName,
                    "wgt_options": JSON.stringify(widgetSettings),
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

let menuArranger = document.getElementsByClassName('menu-arranger')[0];
let menuPickerContainer = document.getElementsByClassName('menu-widget')[0];

if(menuPickerContainer){
    menuPickerContainer.addEventListener('click',  (e) => {
        let el = e.target

        if(el.classList.contains('is-menu-checked')) {
            let checkedItems = el.parentNode.querySelectorAll('input[name=menu-item]:checked');
            if (checkedItems.length > 0){
                checkedItems.forEach(((checkbox, key) => {
                    checkbox.checked = false;
                    let action = checkbox.dataset.action,
                        name = checkbox.dataset.name,
                        slug = checkbox.dataset.slug,
                        url = window.location.href + `?action=${action}&slug=${slug}`;

                    let form = '';
                    if (widgetFormCollected.has(slug)){
                        form = widgetFormCollected.get(slug);
                        if (menuArranger){
                            menuArranger.insertAdjacentHTML('beforeend', generateWidgetForm(name, slug, form))
                        }
                    } else {
                        defaultXHR().Get(url, function (err, data) {
                            if (data) {
                                data = JSON.parse(data);
                                if (data.hasOwnProperty('status') && data.status === 200) {
                                    widgetFormCollected.set(slug, data.data);
                                    form = widgetFormCollected.get(slug);
                                    if (menuArranger){
                                        menuArranger.insertAdjacentHTML('beforeend', generateWidgetForm(name, slug, form))
                                    }
                                }
                            }
                        });
                    }
                }));
            }
        }
    });
}

function generateWidgetForm(name, slug, more) {
    return `<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <span class="width:100% height:100% z-index:hidden-over-draggable draggable-hidden-over"></span>
        <fieldset
            class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center pointer-events:none">
            <legend class="bg:pure-black color:white padding:default pointer-events:none d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">${name}</span>
                <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <form data-widget-form="true"  class="widgetSettings d:none flex-d:column menu-widget-information pointer-events:all owl width:100%">
                <input type="hidden" name="widget_slug" value="${slug}">
                ${more}
                <div class="form-group">
                    <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete Widget Item
                    </button>
                </div>
            </form>
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
    saveMenuChangesForm = document.getElementById('saveMenuWidgetBuilderItems');
if(saveAllMenu && saveMenuChangesForm){
    saveAllMenu.addEventListener('click', function (e) {
        e.preventDefault();
        setListDataArray();
        addHiddenInputToForm(saveMenuChangesForm, 'menuWidgetSlug', menuWidgetSlug);
        addHiddenInputToForm(saveMenuChangesForm, 'menuWidgetDetails', JSON.stringify({
            menuWidgetID: menuWidgetID, // This is the widget_slug that houses the menu items
            menuWidgetSlug: menuWidgetSlug, // This is the widget_slug that houses the menu items
            menuWidgetItems: getListDataArray(),
        }));
        saveMenuChangesForm.submit();
    })
}
