let scripts = document.querySelectorAll("[data-script_path]");
scripts.forEach((script) => {
    myModule.loadScriptDynamically(script.dataset.script_path, script.dataset.script_path).then()
});

let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    fieldChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    fieldFormCollected = new Map(),
    fieldMenuUL = document.querySelector('.field-menu-ul');

let fieldSlug = document.querySelector('input[name="field_slug"]'),
    fieldID = document.querySelector('input[name="field_id"]'),
    fieldSelectionContainer = document.querySelector('.field-selection-container');
if (fieldSlug){
    fieldSlug = fieldSlug.value
}
if (fieldID){
    fieldID = fieldID.value
}

let menuArrangerLi = document.querySelector('.menu-arranger-li');
if (menuArrangerLi){
    try {
        new myModule.MenuToggle('form', new myModule.Query())
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
}

if (document.querySelector(parent)){
    new myModule.Draggables(parent)
        .settings(fieldChild, ['legend'], false) // draggable element
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
            // setListDataArray();
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
}

if(fieldSelectionContainer){
    let fieldContainerButton = fieldSelectionContainer.querySelector('.field-add-button');
    fieldContainerButton.addEventListener('click',  (e) => {
        handleFieldSelection();
    });

}

function handleFieldSelection() {
    if (fieldSelectionContainer){
        let checkedSlug = [];
        let checkedItems = fieldSelectionContainer.querySelectorAll("input[name='field_ids[]']:checked");
        checkedItems.forEach((field) => {
            if (field.dataset.hasOwnProperty('cant_retrieve_field_items') === false){
                checkedSlug.push(field.value);
            }
        });

        if (checkedSlug.length > 0){
            let slug = {
                action: 'getFieldItems',
                fieldSlug: JSON.stringify(checkedSlug)
            }
            let url = window.location.href + "?action=getFieldItems";
            new XHRApi({...{}, ...slug}).Get(url, function (err, data) {
                if (data) {
                    data = JSON.parse(data);
                    insertFieldItems(data, checkedSlug);
                }
            });
        }
    }
}

// delete menu or widget
if (fieldMenuUL){
    fieldMenuUL.addEventListener('click', (e) => {
        let el = e.target;
        if (el.classList.contains('delete-menu-arrange-item')){
            let arranger = el.closest('.draggable');
            if (arranger){
                arranger.remove();
            }
        }
    });
}

function insertFieldItems(data, checkedSlug) {
    if (fieldMenuUL){
        fieldMenuUL.insertAdjacentHTML('beforeend', data.data);
        let draggableElements = fieldMenuUL.querySelectorAll('.draggable');
        checkedSlug.forEach((slug) => {
            draggableElements.forEach((dragEL) => {
                dragEL.insertAdjacentHTML('beforeend', `<input type="hidden" name="field_ids[]" value="${slug}">`);
            })
        });
    }
}