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
    if (tinyAssets) {
        tinyJSAssets = tinyAssets.content.querySelectorAll('.js');
        tinyCSSAssets = tinyAssets.content.querySelectorAll('.css');

        tinyCSSAssets.forEach((css) => {
            content_css += css.value + ',';
        });
        content_css = content_css.slice(0, -1);
    }
    let fieldSelectionManager = '';
    if (tinyAssets){
        fieldSelectionManager = 'tonics-fieldselectionmanager';
    }

    return tinymce.init({
        // add support for image lazy loading
        extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|loading=lazy|decoding=async]," +
            "svg[*],path[*],def[*],script[*],use[*]",
        selector: editorID,
        height: 900,
        menubar: true,
        plugins: [
            'advlist', 'tonics-drivemanager', fieldSelectionManager, 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
        ],
        // fullscreen_native: true,
        toolbar: `undo redo | tonics-drivemanager ${fieldSelectionManager} link image media | ` +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist | help',
        content_style: 'body { font-family:IBMPlexSans-Regular,Times New Roman,serif; font-size:20px }',
        contextmenu: `link image | copy searchreplace tonics-drivemanager | ${fieldSelectionManager} | bold italic blocks align`,
        content_css: content_css,
        body_class: "entry-content",
        remove_trailing_brs: true,
        setup: function (editor) {
            editor.on('init', function (e) {

                editor.getBody().addEventListener('click', (e) => {
                    let target = e.target;
                    if (target.classList.contains('fieldsPreview')){
                        let tabContainer = target.closest('.tabs');
                        let dataToSend = {
                            action: 'fieldPreviewFromEditor',
                            fieldPostDataInEditor: JSON.stringify(getPostData(tabContainer)),
                            fieldTableSlugsInEditor: JSON.stringify(getFieldSlugsTable(tabContainer)),
                        };
                        let url = window.location.href + "?action=fieldPreviewFromEditor";
                        new XHRApi({...{}, ...dataToSend}).Get(url, function (err, data) {
                            if (data) {
                                data = JSON.parse(data);
                                if (data.status === 200 && target.nextElementSibling.classList.contains('fieldsPreviewContent')){
                                    target.nextElementSibling.innerHTML = '';
                                    target.nextElementSibling.insertAdjacentHTML('afterbegin', data.data);
                                }
                            }
                        });
                    }

                    if (target.classList.contains('fieldsDelete')){
                        let tabContainer = target.closest('.tabs');
                        if (tabContainer){
                            myModule.promptToast("Field deletion could be irreversible", "Delete Field", () => {
                                tabContainer.remove();
                            })
                        }
                    }
                });

                editor.getBody().addEventListener('change', (e) => {
                   let input = e.target, tagName = input.tagName;
                   if (tagName.toLowerCase() === 'input'){
                       input.setAttribute('value', input.value);
                       if (input.type === 'checkbox'){
                           (input.checked) ? input.setAttribute('checked', input.checked) : input.removeAttribute('checked');
                       }

                       if(input.type === 'radio'){
                           let parentRadio = input.parentElement;
                           if (parentRadio && parentRadio.querySelectorAll(`input[name="${input.name}"]`).length > 0){
                               parentRadio.querySelectorAll(`input[name="${input.name}"]`).forEach((radio) => {
                                   radio.removeAttribute('checked');
                               });
                           }
                           (input.checked) ? input.setAttribute('checked', input.checked) : input.removeAttribute('checked');
                       }
                   }

                   if (tagName.toLowerCase() === 'textarea'){
                       let text = input.value;
                       input.innerHTML = text;
                       input.value = text;
                   }

                   if (tagName.toLowerCase() === 'select'){
                       input.options[input.selectedIndex].selected = 'selected';
                       input.options[input.selectedIndex].setAttribute('selected', 'selected');
                   }

                });

                if (tinyJSAssets && tinyJSAssets.length > 0) {
                    tinyJSAssets.forEach((js) => {
                        let script = document.createElement("script");
                        script.type = 'module';
                        script.src = js.value;
                        script.async = true;
                        tinymce.activeEditor.dom.select('head')[0].appendChild(script);
                    });
                }

                let svgInline = document.querySelector('.tonics-inline-svg');
                if (svgInline) {
                    svgInline = svgInline.cloneNode(true);
                    editor.getBody().previousElementSibling.insertAdjacentElement('afterbegin', svgInline);
                }

                if (fromOnFullScreenState) {
                    tinymce.execCommand("mceFullScreen", false, e.target.id);
                }
            });

            editor.on('keydown',  (e) => {
                if (e.code === 'Backspace' || e.code === 'Delete')  {
                    console.log('Delete or backspace key pressed!', editor.selection.getContent({ format: "text" }));
                    console.log(e.target);
                   // event.preventDefault();
                   // event.stopPropagation();
                   // return false;
                }
            });

            editor.on('blur', function (e) {
                tinymce.triggerSave();
            });

            editor.on('FullscreenStateChanged', function (e) {
                // hack to get full screen to work from a nested container
                if (fromOnFullScreenState === false) {
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
                        addTiny('#' + IDQuery.id).then(function (editors) {
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

function getPostData(fieldSettingsEl) {
    let widgetSettings = {};
    let elements = fieldSettingsEl.querySelectorAll('input, textarea, select');
    elements.forEach((inputs) => {

        // collect checkbox
        if (inputs.type === 'checkbox'){
            let checkboxName = inputs.name;
            if (!widgetSettings.hasOwnProperty(checkboxName)){
                widgetSettings[checkboxName] = [];
            }
            if (inputs.checked){
                widgetSettings[checkboxName].push(inputs.value);
            }
        }

        if (!widgetSettings.hasOwnProperty(inputs.name)) {
            widgetSettings[inputs.name] = inputs.value;
        }
    });
    return widgetSettings;
}

let tinyEditorsForm = document.getElementById('EditorsForm');
if (tinyEditorsForm){
    tinyEditorsForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (tinymce.activeEditor.getBody().hasChildNodes()) {
            let nodesData = {}, key = 0;
            let bodyNode = tinymce.activeEditor.getBody().childNodes;
            let postData = getPostData(tinymce.activeEditor.getBody());
            bodyNode.forEach((node) => {
                if (node.classList.contains('tonicsFieldTabsContainer')) {
                    if (nodesData.hasOwnProperty(key)) {
                        ++key;
                    }
                    let fieldTableSlug = node.querySelector('input[name="main_field_slug"]');
                    if (fieldTableSlug){
                        fieldTableSlug = fieldTableSlug.value;
                    }
                    nodesData[key] = {
                        fieldTableSlug: fieldTableSlug,
                        raw: false,
                    };
                } else {
                    if (nodesData.hasOwnProperty(key) && nodesData[key].raw === false) {
                        ++key;
                    }

                    let previousContent = (nodesData.hasOwnProperty(key)) ? nodesData[key].content : '';
                    nodesData[key] = {content: previousContent + node.outerHTML, raw: true};
                }
            });

            addHiddenInputToForm(tinyEditorsForm, 'fieldItemsDataFromEditor', JSON.stringify(nodesData));
            addHiddenInputToForm(tinyEditorsForm, 'fieldTableSlugsInEditor', JSON.stringify(getFieldSlugsTable()));
            addHiddenInputToForm(tinyEditorsForm, 'fieldPostDataInEditor', JSON.stringify(postData));
            tinyEditorsForm.submit();
        }
    });
}

function getFieldSlugsTable(el = null) {
    if (el === null){
        el = tinymce.activeEditor.getBody();
    }
    let fieldTables = {};
    el.querySelectorAll('input[name="main_field_slug"]').forEach((table) => {
        fieldTables[table.value] =table.value;
    });
    return fieldTables;
}