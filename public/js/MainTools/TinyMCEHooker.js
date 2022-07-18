hookTinyMCE();

function hookTinyMCE() {
    if (typeof tinymce !== 'undefined') {
        let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
        allTinyArea.forEach(tinyArea => {
            tinyArea.dataset.tinyinstance = 'true';
            tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
            addTiny('#' + tinyArea.id);
        });

        const tinyDialogObserver = new MutationObserver(((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                // added nodes.
                let addedNode = mutation.addedNodes[0];
                if (mutation.addedNodes.length > 0 && addedNode.nodeType === Node.ELEMENT_NODE) {
                    let tinyArea = addedNode.querySelector('.tinyMCEBodyArea');
                    if (tinyArea) {
                        // if tinyInstance is available, re-initialize it
                        if (tinyArea.dataset.tinyinstance === 'true') {
                            let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
                            allTinyArea.forEach(tinyArea => {
                                tinymce.execCommand("mceRemoveEditor", false, tinyArea.id);
                                tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                                addTiny('#' + tinyArea.id);
                            });
                            return;
                        }

                        // else...
                        tinyArea.dataset.tinyinstance = 'true';
                        tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        addTiny('#' + tinyArea.id);
                    }
                }
            }
        }));
        // Start observing the target node for configured mutations
        tinyDialogObserver.observe(document.querySelector('main'), {attributes: false, childList: true, subtree: true});
    }
}

let previousTinyPositionBeforeFullScreenStateChange = null,
    fromOnFullScreenState = false;

function addTiny(editorID) {
    let tinyAssets = document.querySelector('template.tiny-mce-assets'),
        content_css = '',
        tinyJSAssets = null, tinyCSSAssets = null;
        if(tinyAssets){
            tinyJSAssets = tinyAssets.content.querySelectorAll('.js');
            tinyCSSAssets = tinyAssets.content.querySelectorAll('.css');

            tinyCSSAssets.forEach((css) => {
                content_css += css.value + ',';
            });
            content_css = content_css.slice(0, -1);
        }

    return tinymce.init({
        // add support for image lazy loading
        extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|loading=lazy|decoding=async]," +
        "svg[*],path[*],def[*],script[*],use[*]",
        selector: editorID,
        height: 900,
        menubar: true,
        plugins: [
            'advlist', 'tonics-drivemanager', 'tonics-fieldselectionmanager', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
        ],
        noneditable_class: 'tonics-legend',
        editable_class: 'widgetSettings,dropdown-toggle',
        // fullscreen_native: true,
        toolbar: 'undo redo | tonics-drivemanager tonics-fieldselectionmanager link image media | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist | help',
        content_style: 'body { font-family:IBMPlexSans-Regular,Times New Roman,serif; font-size:20px }',
        contextmenu: "link image | copy searchreplace tonics-drivemanager | tonics-fieldselectionmanager | bold italic blocks align",
        content_css : content_css,
        body_class : "entry-content",
        remove_trailing_brs: true,
        setup: function (editor) {
            editor.on('init', function (e) {
                if (tinyJSAssets.length > 0) {
                    tinyJSAssets.forEach((js) => {
                        let script = document.createElement("script");
                        script.type = 'module';
                        script.src = js.value;
                        script.async = true;
                        tinymce.activeEditor.dom.select('head')[0].appendChild(script);
                    });
                }

                let svgInline = document.querySelector('.tonics-inline-svg');
                if (svgInline){
                    svgInline = svgInline.cloneNode(true);
                    editor.getBody().previousElementSibling.insertAdjacentElement('afterbegin', svgInline);
                }

                if (fromOnFullScreenState) {
                    tinymce.execCommand("mceFullScreen", false, e.target.id);
                }
            });
            editor.on('blur', function (e) {
                if (editor.getBody().hasChildNodes()){
                    let nodesData = {}, key = 0;
                    let bodyNode = editor.getBody().childNodes;
                    bodyNode.forEach((node) => {
                        if (node.classList.contains('tonics-field-items-unique') && node.querySelector('.fieldFileHandler')){
                            if (nodesData.hasOwnProperty(key)){
                                ++key;
                            }
                            let fieldHandlerValue = node.querySelector('.fieldFileHandler').dataset.value;
                            setFieldListDataArray(node.querySelectorAll('.draggable'))
                            let fieldSettingsEl = node.querySelectorAll('.widgetSettings');
                            nodesData[key] = {content: node.outerHTML, raw: false, fields: getFieldListDataArray(fieldSettingsEl), fieldHandler: fieldHandlerValue};
                        } else {
                            if (nodesData.hasOwnProperty(key) && nodesData[key].raw === false){
                                ++key;
                            }

                            let previousContent = (nodesData.hasOwnProperty(key)) ?  nodesData[key].content : '';
                            nodesData[key] = {content: previousContent + node.outerHTML, raw: true};
                        }
                    });
                    let inputForFields = document.createElement('input');
                    inputForFields.type = 'hidden'; inputForFields.name = 'fieldItemsDataFromEditor';
                    console.log(nodesData);
                    inputForFields.value = JSON.stringify(nodesData);
                    if (document.querySelector('form')){
                        document.querySelector('form').insertAdjacentElement('afterbegin', inputForFields);
                    }
                }
                tinymce.triggerSave();
            });

            editor.on('FullscreenStateChanged', function (e) {
                // hack to get full screen to work from a nested container
                if (fromOnFullScreenState === false){
                    let tinyArea = e.target.container,
                        tinyID = e.target.id,
                        IDQuery = document.querySelector('#' + tinyID);

                    if (previousTinyPositionBeforeFullScreenStateChange === null) {
                        previousTinyPositionBeforeFullScreenStateChange = tinyArea.parentElement;
                    }
                    if (tinyArea.classList.contains('tox-fullscreen')) {
                        // we add the editor to body first child, this way, fullscreen works with no quirks
                        document.querySelector('body').insertAdjacentElement('afterbegin', IDQuery);
                        tinymce.execCommand("mceRemoveEditor", false, IDQuery.id);
                        IDQuery.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        fromOnFullScreenState = true;
                        addTiny('#' + IDQuery.id).then(function(editors) {
                            // reset for next event, this would be called after editor.on('init')
                            fromOnFullScreenState = false;
                        });
                    } else {
                        // we return the editor back to its position
                        previousTinyPositionBeforeFullScreenStateChange.insertAdjacentElement('beforeend', IDQuery);
                        tinymce.execCommand("mceRemoveEditor", false, IDQuery.id);
                        IDQuery.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        fromOnFullScreenState = false;
                        previousTinyPositionBeforeFullScreenStateChange = null;
                        addTiny('#' + IDQuery.id);
                    }
                }
            });
        }
    });
}

function setFieldListDataArray(draggable) {
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
    }
}

function getFieldListDataArray(fieldSettingsEl) {
        let ListArray = [],
            fieldName = '',
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
                    try {
                        inputs.defaultValue  = inputs.value;
                    }catch (e) {
                    }
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