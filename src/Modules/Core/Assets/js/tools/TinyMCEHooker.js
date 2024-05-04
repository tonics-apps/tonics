
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
                if (mutation.addedNodes.length > 0){
                    mutation.addedNodes.forEach((addedNode =>  {
                        if (addedNode.nodeType === Node.ELEMENT_NODE){
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

                    }));
                }
            }
        }));

        // Start observing the target node for configured mutations
        tinyDialogObserver.observe(document.querySelector('body'), {attributes: false, childList: true, subtree: true});

        try {
            // ..
        } catch (e) {

        }
    }
}

let previousTinyPositionBeforeFullScreenStateChange = null,
    fromOnFullScreenState = false,
    currentEditedInputInTonicsFieldTab = null;

function addTiny(editorID) {
    let fieldUniqueSlug = '';
    fieldUniqueSlug = document.querySelector(editorID)?.dataset?.field_unique_slug;
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

    let onClick = '';

    return tinymce.init({
        // add support for image lazy loading
        extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|loading=lazy|decoding=async]," +
            "svg[*],path[*],def[*],script[*],use[*]",
        selector: editorID,
        height: 650,
        menubar: true,
        plugins: [
            'advlist', 'tonics-drivemanager', fieldSelectionManager, 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'autosave'
        ],
        autosave_ask_before_unload: true,
        autosave_prefix: 'tonics-tinymce-autosave-{path}{query}-' + fieldUniqueSlug + '-',
        autosave_interval: '5s',
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
            if (!window.hasOwnProperty('TonicsScript')){ window.TonicsScript = {};}
            if (!window.TonicsScript.hasOwnProperty('tinymce')){ window.TonicsScript.tinymce = [] }
            window.TonicsScript.tinymce.push(editor);
            editor.on('init', function (e) {
                if (tinyJSAssets && tinyJSAssets.length > 0) {
                    tinyJSAssets.forEach((js) => {
                        let script = document.createElement("script");
                        script.type = 'module';
                        script.src = js.value;
                        script.async = true;
                        tinymce.activeEditor.dom.select('head')[0].appendChild(script);
                    });
                }
                editor.getBody().addEventListener('click', (e) => {
                    let target = e.target;
                    onClick = e;

                    if (target.classList.contains('tonicsFieldTabsContainer')) {
                        let tabContainer = target.closest('.tabs');
                        tabContainer.dataset.tonics_selected = '1';
                    }

                    if (target.classList.contains('fieldsPreview')) {
                        let tabContainer = target.closest('.tabs');
                        if (window.parent?.TonicsEvent?.EventDispatcher && window.parent.TonicsEvent?.EventConfig){
                            let tonicsFieldWrapper = tabContainer.querySelector('.tonicsFieldWrapper');
                            let jsonValue = tonicsFieldWrapper.value;
                            const OnBeforeTonicsFieldPreview = new OnBeforeTonicsFieldPreviewEvent(jsonValue, target);
                            let eventDispatcher = window.TonicsEvent.EventDispatcher;
                            eventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnBeforeTonicsFieldPreview, OnBeforeTonicsFieldPreviewEvent);
                            if (OnBeforeTonicsFieldPreview.canRequest()){
                                OnBeforeTonicsFieldPreview.loadAnimation(target)
                                fieldPreviewFromPostData(OnBeforeTonicsFieldPreview.getPostData(), function (data) {
                                    if (data.status === 200 && target.nextElementSibling.classList.contains('fieldsPreviewContent')) {
                                        target.nextElementSibling.innerHTML = '';
                                        target.nextElementSibling.insertAdjacentHTML('afterbegin', data.data);
                                    }
                                })
                            }
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

                let svgInline = document.querySelector('.tonics-inline-svg');
                if (svgInline) {
                    svgInline = svgInline.cloneNode(true);
                    editor.getBody().previousElementSibling.insertAdjacentElement('afterbegin', svgInline);
                }

                if (fromOnFullScreenState) {
                    tinymce.execCommand("mceFullScreen", false, e.target.id);
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

function fieldPreviewFromPostData(postData, onSuccess = null, onError = null) {
    let url = "/admin/tools/field/field-preview";
    let defaultHeader = {
        'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
    };
    let dataToSend = {
        'postData': postData
    }
   new XHRApi({...defaultHeader}).Post(url, JSON.stringify(dataToSend), function (err, data) {
        if (data) {
            data = JSON.parse(data);
            if (onSuccess) {
                onSuccess(data);
            }
        } else {
            if (onError) {
                onError();
            }
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
        }else if (inputs.type === 'select-multiple'){
            let selectOptions = inputs.options;
            let selectBoxName = inputs.name;
            for (let k = 0; k < selectOptions.length; k++) {
                let option = selectOptions[k];
                if (option.selected){
                    if (!widgetSettings.hasOwnProperty(selectBoxName)){
                        widgetSettings[selectBoxName] = [];
                    }

                    widgetSettings[selectBoxName].push(option.value || option.text);
                }
            }
        }else if (!widgetSettings.hasOwnProperty(inputs.name)) {
            widgetSettings[inputs.name] = inputs.value;
        }
    });
    return widgetSettings;
}

class CollatePostContentFieldItemsOnFieldsEditorsSubmit {
    /** @type OnSubmitFieldEditorsFormEvent */
    fieldSubmitEvObj = null;
    event = null;
    constructor(event) {
        this.event = event;
        this.fieldSubmitEvObj = event;
        this.handleTinymceChildNodes();
    }

   handleTinymceChildNodes() {
        let self = this;
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.getBody().hasChildNodes()) {
            let nodesData = {}, key = 0;
            let bodyNode = tinymce.activeEditor.getBody().childNodes;
            bodyNode.forEach((node) => {
                if (node.classList.contains('tonicsFieldTabsContainer')) {
                    if (nodesData.hasOwnProperty(key) &&  window.parent?.TonicsEvent?.EventDispatcher && window.parent.TonicsEvent?.EventConfig) {
                        ++key;
                    }

                    let postData = {};
                    let elements = node.querySelectorAll('input, textarea, select');
                    elements.forEach((inputs) => {
                        self.fieldSubmitEvObj.getInputData(inputs, postData);
                    });

                    let tonicsFieldWrapper = node.querySelector('.tonicsFieldWrapper');
                    let jsonValue = tonicsFieldWrapper.value;
                    const OnBeforeTonicsFieldSubmit = new OnBeforeTonicsFieldSubmitEvent(jsonValue, node);
                    let eventDispatcher = window.TonicsEvent.EventDispatcher;
                    eventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnBeforeTonicsFieldSubmit, OnBeforeTonicsFieldSubmitEvent);
                    let postDataFromBeforeTonicsFieldSubmit = OnBeforeTonicsFieldSubmit.getPostData();
                    nodesData[key] = {
                        raw: false,
                        postData: postDataFromBeforeTonicsFieldSubmit,
                        // previewFrag: node.querySelector('.fieldsPreviewContent')?.innerHTML
                    };
                } else {
                    if (nodesData.hasOwnProperty(key) && nodesData[key].raw === false) {
                        ++key;
                    }

                    let previousContent = (nodesData.hasOwnProperty(key)) ? nodesData[key].content : '';
                    nodesData[key] = {content: previousContent + node.outerHTML, raw: true};
                }
            });

            self.event.addHiddenInputToForm(self.event.editorsForm, 'fieldItemsDataFromEditor', JSON.stringify(nodesData));
        }
    }
}

if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnSubmitFieldEditorsFormEvent.push(...[CollatePostContentFieldItemsOnFieldsEditorsSubmit]);
}

class OnBeforeTonicsFieldPreviewEvent {
    get elementTarget() {
        return this._elementTarget;
    }

    set elementTarget(value) {
        this._elementTarget = value;
    }
    get postData() {
        return this._postData;
    }

    set postData(value) {
        this._postData = value;
    }

    get request() {
        return this._canRequest;
    }

    set request(value) {
        this._canRequest = value;
    }

    constructor(postData, target) {
        this._postData = postData;
        this._elementTarget = target;
        this._canRequest = true;
    }

    canRequest() {
        return this._canRequest;
    }

    getCSFRToken(){
        return getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])
    }

    getPostData() {
        return this._postData;
    }

    getElementTarget() {
        return this._elementTarget;
    }

    loadAnimation(target) {
        target.nextElementSibling.innerHTML = '<span class="loading-animation"></span>';
    }

    removeAnimation(target) {
        target.nextElementSibling.innerHTML = '';
    }
}

class OnBeforeTonicsFieldSubmitEvent {
    get elementTarget() {
        return this._elementTarget;
    }

    set elementTarget(value) {
        this._elementTarget = value;
    }

    get postData() {
        return this._postData;
    }

    set postData(value) {
        this._postData = value;
    }

    postData = null; elementTarget = null;

    constructor(postData, target) {
        this._postData = postData;
        this._elementTarget = target;
    }

    getPostData() {
        return this._postData;
    }

    getElementTarget() {
        return this._elementTarget;
    }
}