
let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    widgetChild = `.menu-arranger-li`,
    right = false, left = false, top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    menuSlug = document.querySelector('input[name="menu_slug"]').value,
    menuID = document.querySelector('input[name="menu_id"]').value;

new MenuToggle('.menu-widget', new Query())
    .settings('.menu-box-li', '.dropdown-toggle', '.child-menu')
    .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
    .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
    .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
    .closeOnClickOutSide(true)
    .run();

new MenuToggle('.menu-arranger', new Query())
    .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
    .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
    .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
    .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
    .closeOnClickOutSide(true)
    .run();

new Draggables(parent)
    .settings(widgetChild, ['.menu-widget-information', 'legend'], false) // draggable element
    .onDragDrop(function (element, self) {
        // to the right
        let nestedRight = document.querySelector(parent).querySelector('.nested-to-the-right');

        let elementDragged = self.getDragging().closest(widgetChild);
        let elementDropped = self.getDroppedTarget()?.closest(widgetChild);

        // to the left
        let nestedLeft = document.querySelector(parent).querySelector('.nested-to-the-left');
        if (right) {
            if (nestedRight && nestedRight.querySelector('.menu-arranger-li-sub')) {
                nestedRight.querySelector('.menu-arranger-li-sub').insertAdjacentElement('beforeend', elementDragged);
                removeDraggableDirections();
            }
            right = false;
        }else if (left && nestedLeft) {
            nestedLeft.insertAdjacentElement('afterend', elementDragged);
            removeDraggableDirections();
            left = false;
        } else {
            if (elementDropped !== elementDragged && top || bottom){
                // swap element
                swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
                sensitivity = 0;
                top = false; bottom = false;
            }
        }

        setListDataArray();
    }).onDragRight((element) => {
        if (sensitivity++ >= sensitivityMax){
            let toTheRight = element.previousElementSibling;
            if (toTheRight && toTheRight.classList.contains('menu-arranger-li')) {
                right = true;
                toTheRight.classList.add('nested-to-the-right')
            }
            sensitivity = 0;
        }
}).onDragLeft( (element) => {
    if (sensitivity++ >= sensitivityMax){
        let toTheLeft = element.parentElement.parentElement;
        if (toTheLeft && toTheLeft.classList.contains('menu-arranger-li')) {
            left = true;
            toTheLeft.classList.add('nested-to-the-left');
        }
        sensitivity = 0;
    }
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

function removeDraggableDirections() {
    if(draggable){
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            draggable[i].classList.remove('drag-to-the-bottom', 'drag-to-the-top', 'nested-to-the-left', 'nested-to-the-right');
        }
    }
}

function setListDataArray() {
    if(draggable){
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            draggable[i].setAttribute("data-id", i + 1); // add ID's to all draggable item
            let parentID = null;
            parentID = draggable[i].parentElement.parentElement.getAttribute("data-id");
            draggable[i].setAttribute("data-parentid",
                (draggable[i].parentElement.classList.contains('menu-arranger-li-sub'))  ? parentID : null)
        }
    }
}

function getListDataArray() {
    if(draggable){
        let ListArray = []
        let ListPermissions = []
        let ID = null, parentID = null, menuName = null, classes = null, urlSlug = null, svgIcon = null, linkTarget = null, defaultMenuName = null;
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            ID = draggable[i].getAttribute('data-id');
            parentID = draggable[i].getAttribute('data-parentid');
            // If user overwrites the menu name, store that, otherwise, store the default name
            if(draggable[i].querySelector('input[name="menu-name"]').value ){
                menuName = draggable[i].querySelector('input[name="menu-name"]').value;
            }else { menuName = draggable[i].querySelector('.menu-root-name').value }

            classes = draggable[i].querySelector('input[name="menu-item-classes"]').value;
            urlSlug = draggable[i].querySelector('input[name="url-slug"]').value;
            svgIcon = draggable[i].querySelector('input[name="svg-icon"]').value;
            linkTarget = draggable[i].querySelector('select[name="linkTarget"]').value;
            let menuPermission = draggable[i].querySelector('select[name="menuPermissions"]');
            let permissions = [...menuPermission.options].filter(option => option.selected).map(option => option.value);

            // This gets the data ID and ParentID of each list ;)
            let menuObject = {
                "fk_menu_id": menuID,
                "mt_id": i+1,
                "mt_parent_id": (draggable[i].parentElement.classList.contains('menu-arranger-li-sub')) ? parentID : null,
                "slug_id": crypto.randomUUID(),
                "mt_name": menuName,
                "mt_icon": svgIcon,
                "mt_url_slug": urlSlug,
                "mt_classes": classes,
                // 0 stands for same tab, and 1 stands for new tab
                "mt_target": linkTarget,
            }
            ListArray.push(menuObject);

            if (permissions.length > 0){
                permissions.forEach((perm) => {
                    ListPermissions.push({
                        "fk_menu_item_slug_id": menuObject.slug_id,
                        "fk_permission_id": perm,
                    });
                })
            }

        }

        return {
          menuItems: ListArray,
          permissions: ListPermissions,
        };
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

        // MORE BUTTON
        if (el.classList.contains('more-button')){
            let action = el.dataset.action,
                url = el.dataset.morepageurl;
            defaultXHR(el.dataset).Get(url, function (err, data) {
                if (data){
                    data = JSON.parse(data);
                    if (data.hasOwnProperty('status') && data.status === 200) {
                        let ul = el.closest('.menu-box-checkbox-items').querySelector('ul'),
                            moreButton = ul.querySelector('.more-button'),
                            lastMenuItem = ul.querySelector('li:nth-last-of-type(1)');
                        if (moreButton){
                            moreButton.remove();
                        }
                        lastMenuItem.insertAdjacentHTML('afterend', data.data);
                    }
                }
            });
        }

        if(el.classList.contains('is-menu-checked')) {
            let checkedItems = el.parentNode.querySelectorAll('input[name=menu-item]:checked');
            if (checkedItems.length > 0){
                checkedItems.forEach(((checkbox, key) => {
                    checkbox.checked = false;
                    let url = checkbox.dataset.url_slug;
                    let name = checkbox.dataset.name;

                    if (menuArranger){
                        menuArranger.insertAdjacentHTML('beforeend', `
                            <li tabindex="0" class="width:100% draggable menu-arranger-li cursor:move no-text-highlight">
            <fieldset class="width:100% padding:default d:flex justify-content:center pointer-events:none">
                <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                    <span class="menu-arranger-text-head">${name}</span>
                    <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                        <svg class="icon:admin tonics-arrow-down color:white">
                            <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                        </svg>
                    </button>
                </legend>
                <div class="d:none flex-d:column menu-widget-information pointer-events:all owl width:100%">

                    <div class="form-group d:flex flex-gap:small">
                        <label class="menu-settings-handle-name width:100%">Overwrite Name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                            name="menu-name" value='${name}' placeholder="Overwrite the menu name">
                        </label>
                        
                        <label class="menu-settings-handle-name width:100%">SVG Icon Name
                            <input id="menu-name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="svg-icon" value="" placeholder="e.g toggle-right">
                        </label>
                    </div>

                    <div class="form-group d:flex flex-gap:small">
                        <label class="menu-settings-handle-name width:100%">Overwrite URL Slug
                            <input id="menu-url-slug" type="text" class="menu-url-slug color:black border-width:default border:black placeholder-color:gray" 
                            name="url-slug" value='${url}' placeholder="Only Overwrite For a Custom Link">
                        </label>
                        
                        <label class="menu-settings-handle-name width:100%">Optional CSS Classes
                            <input id="edit-menu-item" type="text" class="edit-menu-item-classes color:black border-width:default border:black placeholder-color:gray" name="menu-item-classes" value="" placeholder="Separate By Spaces, e.g class-1 class-2">
                        </label>
                    </div>

                    <div class="form-group">
                        <label> Link Target
                            <select name="linkTarget" class="default-selector">
                                <option value="0" selected="selected" disabled="">Link Target</option>
                                <option value="0">Same Tab</option>
                                <option value="1">New Tab</option>
                            </select>
                        </label>
                    </div>

                    <div class="form-group">
                        <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                            Delete Menu Item
                        </button>
                    </div>
                </div>
            </fieldset>
            <ul class="menu-arranger-li-sub width:90%">
            </ul>
        </li>
                    `);
                    }

                }));
            }
        }
    });
}

let searchMenuBoxItem = document.querySelectorAll('.menu-box-item-search'),
    searchBoxInitials = [];

searchMenuBoxItem.forEach(((value, key) => {
    searchBoxInitials[value.dataset.menuboxname] = value.parentElement.cloneNode(true);
}));

if (menuPickerContainer){
    menuPickerContainer.addEventListener('keyup', (e) => {
        let el = e.target;
        if (el.classList.contains('menu-box-item-search')){
            let value = el;
            if (e.code === 'Enter'){
                let searchInputValue = value.value;
                searchInputValue = searchInputValue.trim();
                if (searchInputValue.length > 0 && value.dataset.hasOwnProperty('searchvalue')){
                    value.dataset.searchvalue = searchInputValue;
                    let url = value.dataset.query + encodeURIComponent(searchInputValue);
                    defaultXHR(value.dataset).Get(url, function (err, data) {
                        if (data){
                            data = JSON.parse(data);
                            if (data.hasOwnProperty('status') && data.status === 200) {
                                let parentElement = value.parentElement;
                                let realSearchInput = value.cloneNode(true);
                                value.parentElement.innerHTML = data.data;
                                parentElement.prepend(realSearchInput);
                            }
                        }
                    });
                }
            }
        }
    })

    menuPickerContainer.addEventListener('input', (e) => {
        let el = e.target,
            value = el;
        if (el.classList.contains('menu-box-item-search')){
            let searchInputValue = value.value;
            searchInputValue = searchInputValue.trim();
            if (searchInputValue === ""){
                let parentElement = value.parentElement;
                parentElement.innerHTML = searchBoxInitials[value.dataset.menuboxname].innerHTML;
            }
        }
    })
}

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
    })
}


// save menu builder
if (typeof saveAllMenu === 'undefined') {
    var saveAllMenu = document.querySelector('.tonics-save-changes');
}
let saveMenuChangesForm = document.getElementById('saveMenuBuilderItems');
if(saveAllMenu && saveMenuChangesForm){
    saveAllMenu.addEventListener('click', function (e) {
        e.preventDefault();
        setListDataArray();
        addHiddenInputToForm(saveMenuChangesForm, 'menuSlug', menuSlug);
        let listDataArray = getListDataArray();
        addHiddenInputToForm(saveMenuChangesForm, 'menuDetails', JSON.stringify({
            menuID: menuID, // This is the menu_slug that houses the menu items
            menuSlug: menuSlug, // This is the menu_slug that houses the menu items
            menuItems: listDataArray.menuItems,
            menuItemPermissions: listDataArray.permissions,
        }));
        saveMenuChangesForm.submit();
    })
}
