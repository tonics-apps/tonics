/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

class DataTable {

    parentElement = '';
    apiEntry = '';
    scrollToBottomLockPing = 0;
    shiftClick = new Map();
    currentEditor = null;
    hasThElement = false;
    hasTdElement = false;
    hasTrElement = false;

    lockedSelection = false;

    tdElementChildBeforeOpen = null;

    editingElementsCloneBeforeChanges = new Map();
    editingElements = new Map();
    deletingElements = new Map();

    constructor($parentElement, apiEntry = '') {
        this.parentElement = document.querySelector($parentElement)
        this.apiEntry = apiEntry;
        if (this.parentElement) {
            this.resetListID();
        }
    }

    get thElement() {
        return this._thElement;
    }

    set thElement(value) {
        this.hasThElement = !!value; // True if value is not empty, otherwise, false
        this._thElement = value;
    }

    get tdElement() {
        return this._tdElement;
    }

    set tdElement(value) {
        this.hasTdElement = !!value; // True if value is not empty, otherwise, false
        this._tdElement = value;
    }

    get trElement() {
        return this._trElement;
    }

    set trElement(value) {
        this.hasTrElement = !!value; // True if value is not empty, otherwise, false
        this._trElement = value;
    }

    getParentElement() {
        return this.parentElement;
    }

    getApiEntry() {
        return this.apiEntry;
    }

    boot() {
        if (this.getParentElement()) {

            function sendDoubleClickEvent(e, self) {
                let el = e.target;
                let OnDoubleClick = new OnDoubleClickEvent(el, self);
                self.trElement = el.closest('tr');
                self.tdElement = el.closest('td');
                self.thElement = self.findCorrespondingTableHeader(el);
                self.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnDoubleClick, OnDoubleClickEvent);
            }

            // Double Click For Mobile
            if (!this.getParentElement().hasAttribute("data-event-dblclick-mobile")) {
                this.getParentElement().setAttribute('data-event-dblclick-mobile', 'true');
                // Double Tap For mobile screen:
                // Time in milliseconds to detect a double tap
                const DOUBLE_TAP_TIME = 500;
                let lastTap = 0;
                this.getParentElement().addEventListener('touchstart', (e) => {
                    // e.preventDefault();
                    const currentTime = Date.now();
                    const tapLength = currentTime - lastTap;

                    if (tapLength < DOUBLE_TAP_TIME && tapLength > 0) {
                        sendDoubleClickEvent(e, this);
                    }
                    lastTap = currentTime;
                });
            }

            // For Click Event
            if (!this.getParentElement().hasAttribute("data-event-click")) {
                this.getParentElement().setAttribute('data-event-click', 'true');

                const DOUBLE_TAP_TIME = 200;
                let lastClick = 0;

                this.getParentElement().addEventListener('click', (e) => {
                    let el = e.target;
                    let trEl = el.closest('tr');
                    this.trElement = trEl;
                    this.tdElement = el.closest('td');

                    // Form Filter Button
                    if (el.dataset.menuAction === 'FilterEvent') {
                        e.preventDefault();
                    }

                    let isOpen = el.closest('.data_table_is_open')
                    if (isOpen) {
                        return false;
                    }

                    if (e.shiftKey) {
                        this.resetPreviousTrState()
                        this.setShiftClick(trEl);
                        let Click = new OnShiftClickEvent(el, this);
                        this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, Click, OnShiftClickEvent);
                        return false;
                    } else if (e.ctrlKey) {
                        (trEl.classList.contains('highlight')) ? this.unHighlightTr(trEl) : this.highlightTr(trEl);
                        return false;
                    } else {
                        if (!el.closest('.dataTable-menus')) {
                            // this is a norm mouse click
                            this.resetPreviousTrState();
                            this.highlightTr(trEl);

                            // for shift key
                            this.resetShiftClick();
                            this.setShiftClick(trEl);
                        }

                        let Click = new OnClickEvent(el, this);
                        this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, Click, OnClickEvent);

                    }
                });
            }

            // For Double-Click Event
            if (!this.getParentElement().hasAttribute("data-event-dblclick")) {
                this.getParentElement().setAttribute('data-event-dblclick', 'true');

                this.getParentElement().addEventListener('dblclick', (e) => {
                    sendDoubleClickEvent(e, this);
                });
            }

            // For Scroll Bottom
            if (!this.getParentElement().hasAttribute("data-event-scroll-bottom")) {
                this.getParentElement().setAttribute('data-event-scroll-bottom', 'true');
                this.getParentElement().addEventListener('scroll', (e) => {
                    let el = e.target;
                    let scrollDownwards = el.scrollHeight - el.scrollTop;
                    // the 400 gives us time to react quickly that the scroll is almost/at the bottom
                    let clientHeight = el.clientHeight + 500;

                    // almost at the bottom
                    if (scrollDownwards < clientHeight) {
                        ++this.scrollToBottomLockPing;
                        if (this.scrollToBottomLockPing === 1) {
                            let OnBeforeScrollBottom = new OnBeforeScrollBottomEvent(el, this);
                            OnBeforeScrollBottom.trElement = el.closest('tr');
                            this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnBeforeScrollBottom, OnBeforeScrollBottomEvent);
                        }
                    }

                    // at the bottom
                    if (scrollDownwards === el.clientHeight) {
                        this.scrollToBottomLockPing = 0; // reset ping
                        let OnBeforeTonicsFieldSubmit = new OnScrollBottomEvent(el, this);
                        this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnBeforeTonicsFieldSubmit, OnScrollBottomEvent);
                    }
                });
            }
        }
    }

    /**
     * This gets all the column of the current table header,
     * optionally you can pass the thElement, the trElement and thsElement
     * @param thElement
     * (optional) The main table header element we are looking for its columns
     * @param trsElements
     * (optional) The table rows Elements
     * @param thsElements
     * (optional) The table headers element
     * @returns {*[]}
     */
    getThElementColumns(thElement = null, trsElements = null, thsElements = null) {

        let ths = thsElements;
        if (!ths) {
            ths = this.parentElement.getElementsByTagName('th');
        }

        let trs = trsElements;
        if (!trs) {
            trs = this.parentElement.getElementsByTagName('tr');
        }

        let thEl = thElement;
        if (!thEl) {
            thEl = this.thElement;
        }

        let thID = null;
        let columns = [];
        if (thEl) {
            for (let i = 0; i < ths.length; i++) {
                if (ths[i] === thEl) {
                    thID = i;
                    break;
                }
            }

            for (let i = 0; i < trs.length; i++) {
                columns.push(trs[i].children[thID]);
            }
        }

        return columns;
    }

    getAllThElements() {
        return this.parentElement.querySelector('table > thead > tr').querySelectorAll('th');
    }

    /**
     * Returns an array of the Table headers name
     * @returns {*[]}
     */
    getThHeaders() {
        let headers = [];
        this.getAllThElements().forEach(header => {
            headers.push(header.dataset?.slug)
        });
        return headers;
    }

    getAllSelectTableRow() {
        return this.parentElement.querySelectorAll('.highlight');
    }

    resetEditingState() {
        this.editingElementsCloneBeforeChanges.clear();
        this.editingElements.clear();
        this.deletingElements.clear();
        this.tdElementChildBeforeOpen = null;
    }

    resetEditingElements() {
        this.editingElementsCloneBeforeChanges.clear();
        this.editingElements.clear();
        this.tdElementChildBeforeOpen = null;
        let editing = this.parentElement.querySelectorAll('.editing');
        if (editing) {
            editing.forEach(edit => {
                edit.classList.remove('editing');
            });
        }
    }

    removeDeletingElements() {
        this.deletingElements.clear();
        let deleting = this.parentElement.querySelectorAll('.deleting');
        if (deleting) {
            deleting.forEach(deleteEl => {
                deleteEl.remove();
            });
            this.resetListID();
        }
    }

    getDeletingElements() {
        return this.parentElement.querySelectorAll('.deleting');
    }

    menuActions() {
        return {
            SAVE_EVENT: "SaveEvent",
            CANCEL_EVENT: "CancelEvent",
            DELETE_EVENT: "DeleteEvent",
            UPDATE_EVENT: "UpdateEvent",
            APP_UPDATE_EVENT: "AppUpdateEvent",
            COPY_FIELD_ITEMS_EVENT: "CopyFieldItemsEvent",
            TONICS_APP_STORE_PURCHASE: "TonicsAppStorePurchase",
        }
    }

    apiEvents() {
        return {
            LOAD_MORE_EVENT: "LoadMoreEvent",
            SAVE_EVENT: "SaveEvent",
            DELETE_EVENT: "DeleteEvent",
            UPDATE_EVENT: "UpdateEvent",
            APP_UPDATE_EVENT: "AppUpdateEvent",
            UPSERT_EVENT: "UpsertEvent",
            FILTER_EVENT: "FilterEvent",
            COPY_FIELD_ITEMS_EVENT: "CopyFieldItemsEvent",
        }
    }

    activateMenus($listOfMenuToActivate) {
        let dataTableMenu = this.parentElement.querySelector('.dataTable-menus');
        $listOfMenuToActivate.forEach(function (value) {
            let eventMenu = dataTableMenu.querySelector(`[data-menu-action="${value}"]`);
            if (eventMenu) {
                eventMenu.closest('.menu-item').classList.remove('deactivate-menu');
            }
        });
    }

    deActivateMenus($listOfMenuToActivate) {
        let dataTableMenu = this.parentElement.querySelector('.dataTable-menus');
        $listOfMenuToActivate.forEach(function (value) {
            let eventMenu = dataTableMenu.querySelector(`[data-menu-action="${value}"]`);
            if (eventMenu) {
                eventMenu.closest('.menu-item').classList.add('deactivate-menu');
            }
        });
    }

    getSelectedTrElement() {
        return this.getParentElement().querySelector('.highlight');
    }

    getAllSelectedTrElement() {
        return this.getParentElement().querySelectorAll('.highlight');
    }

    getEventDispatcher() {
        return window.TonicsEvent.EventDispatcher;
    }

    getCurrentEditor() {
        return this.currentEditor;
    }

    /**
     * Credit: https://stackoverflow.com/a/46139306 @ https://stackoverflow.com/users/104380/vsync
     * Modified: DevsrealmGuy
     * @param tdNode
     * @returns {HTMLTableCellElement}
     */
    findCorrespondingTableHeader(tdNode) {
        let i;
        let idx = [...tdNode.parentNode.children].indexOf(tdNode); // get td index

        if (tdNode.closest('table')?.tHead) {
            let thCells = tdNode.closest('table').tHead.rows[0].cells, // get all th cells
                th_colSpan_acc = 0; // accumulator

            // iterate all th cells and add-up their colSpan value
            for (i = 0; i < thCells.length; i++) {
                th_colSpan_acc += thCells[i].colSpan
                if (th_colSpan_acc >= (idx + tdNode.colSpan)) break
            }

            return thCells[i]
        }

        return null;
    }

    collateTdFromTrAndPushToSaveTo(trElements, saveTo, headers) {
        let length = trElements.size ?? trElements.length;
        if (length > 0) {
            trElements.forEach(edit => {
                let tdData = {};
                for (let i = 0; i < edit.cells.length; i++) {
                    if (headers[i]) {
                        tdData[headers[i]] = edit.cells[i].innerText;
                    }
                }
                saveTo.push(tdData);
            });
        }
    }

    resetListID() {
        let tableRows = this.getParentElement().querySelectorAll('tbody > tr');
        if (tableRows && tableRows.length > 0) {
            let list_id = 0;
            tableRows.forEach(tr => {
                tr.dataset.list_id = `${list_id}`;
                ++list_id;
            });
        }

    }

    resetPreviousTrState() {
        this.parentElement.querySelectorAll('[data-list_id]').forEach(trEl => {
            this.unHighlightTr(trEl);
        });
    }

    unHighlightTr(trEl) {
        if (!this.lockedSelection) {
            trEl.classList.remove('highlight');
        }
    }

    highlightTr(trEl) {
        if (this.hasTrElement) {
            let checkBox = trEl.querySelector('[data-checkbox_select]');
            if (checkBox) {
                checkBox.setAttribute('checked', 'true');
            }
            trEl.classList.add('highlight');
        }
    }

    resetShiftClick() {
        this.shiftClick = new Map();
    }

    setShiftClick(trEl) {
        if (this.hasTrElement) {
            this.highlightTr(trEl);
            let id = trEl.dataset.list_id;

            // remove file that have previously been set, so, they can be pushed below
            if (this.shiftClick.get(id)) {
                this.shiftClick.delete(id);
            }
            this.shiftClick.set(id, trEl);
            if (this.shiftClick.size >= 2) {
                // this is getting the first and last shift clicked item, and we're sorting the integer
                let firstItem = [...this.shiftClick][0][0],
                    lastItem = [...this.shiftClick][this.shiftClick.size - 1][0],
                    listIDToLoop = [firstItem, lastItem];
                let sortedData = listIDToLoop.sort((a, b) => {
                    return a.localeCompare(b, undefined, {numeric: true})
                });

                // loop over the sorted ranges. and highlight 'em
                let tBody = this.parentElement.querySelector('tbody');
                for (let i = parseInt(sortedData[0]); i <= parseInt(sortedData[1]); i++) {
                    // highlight file
                    let trEl = tBody.querySelector(`[data-list_id="${i}"]`);
                    if (trEl) {
                        this.highlightTr(trEl);
                    }
                }
            }
        }
    }

    /**
     * @param dataToSend
     * @param onSuccess
     * @param onError
     */
    sendPostRequest(dataToSend = null, onSuccess = null, onError = null) {
        let defaultHeader = {
            'Tonics-CSRF-Token': `${getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
        };

        new XHRApi({...defaultHeader}).Post(this.getApiEntry(), JSON.stringify(dataToSend), function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (data.status === 200) {
                    onSuccess(data);
                } else {
                    onError(data);
                }
            }

            if (err) {
                onError(err);
            }
        });
    }

    getDataTableFormFilterEl() {
        return this.parentElement.querySelector('.dataTable-Form');
    }

    getPostData(el) {
        let elSettings = {};
        let elements = el.querySelectorAll('input, textarea, select');
        elements.forEach((inputs) => {

            // collect checkbox
            if (inputs.type === 'checkbox') {
                let checkboxName = inputs.name;
                if (!elSettings.hasOwnProperty(checkboxName)) {
                    elSettings[checkboxName] = [];
                }
                if (inputs.checked) {
                    elSettings[checkboxName].push(inputs.value);
                }
            }

            if (!elSettings.hasOwnProperty(inputs.name)) {
                elSettings[inputs.name] = inputs.value;
            }
        });
        return elSettings;
    }
}

//----------------
//--- ABSTRACT CLASSES
//----------------
class DataTableAbstractAndTarget {

    hasTrElement = false;
    hasTdElement = false;

    constructor(target, dataTableClass) {
        this._elementTarget = target;
        this._dataTable = dataTableClass;
    }

    get elementTarget() {
        return this._elementTarget;
    }

    set elementTarget(value) {
        this._elementTarget = value;
    }

    get dataTable() {
        return this._dataTable;
    }

    set dataTable(value) {
        this._dataTable = value;
    }

    getElementTarget() {
        return this._elementTarget;
    }
}

class DataTableEditorAbstract {

    hasTdElement = false;
    editorElement = null;

    get tdElement() {
        return this._tdElement;
    }

    set tdElement(value) {
        this.hasTdElement = !!value; // True if value is not empty, otherwise, false
        this._tdElement = value;
    }

    get dataTable() {
        return this._dataTable;
    }

    set dataTable(value) {
        this._dataTable = value;
    }

    /**
     * Create an input element
     * @param type
     * @param value
     * @returns {HTMLInputElement}
     */
    createInput(type = 'text', value = '') {
        let input = document.createElement('input');
        input.classList.add('data_table_is_open');
        input.type = type;
        input.defaultValue = value;
        input.value = value;
        return input;
    }

    editorName() {
        return 'text';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            this.editorElement = this.createInput('text', tdValue);
            this.tdElement.innerHTML = this.editorElement.outerHTML;
        }
    }

    closeEditor() {
        if (this.hasTdElement && this.tdElement.querySelector('input')) {
            let inputValue = this.tdElement.querySelector('input').value;
            this.tdElement.querySelector('input').remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {

    }
}

//----------------------
//--- BUILT-IN EDITORS
//----------------------

class DataTabledEditorNumber extends DataTableEditorAbstract {
    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let input = this.createInput('number', tdValue);
            input.classList.add('data_table_is_open')
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    closeEditor() {
        return super.closeEditor();
    }

    editorValidation() {

    }
}

class DataTabledEditorDate extends DataTableEditorAbstract {

    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let input = this.createInput('date', tdValue);
            input.classList.add('data_table_is_open');
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorDateLocal extends DataTableEditorAbstract {

    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            tdValue.replace(' ', 'T');
            let input = this.createInput('datetime-local', tdValue);
            input.classList.add('data_table_is_open');
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    closeEditor() {
        if (this.hasTdElement && this.tdElement.querySelector('input')) {
            let inputValue = this.tdElement.querySelector('input').value;
            this.tdElement.querySelector('input').remove();
            this.tdElement.innerHTML = inputValue.replace('T', ' ', inputValue);
            this.editorElement = null;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorDateMonth extends DataTableEditorAbstract {

    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let input = this.createInput('month', tdValue);
            input.classList.add('data_table_is_open');
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorDateWeek extends DataTableEditorAbstract {

    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let input = this.createInput('week', tdValue);
            input.classList.add('data_table_is_open');
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorDateTime extends DataTableEditorAbstract {

    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let input = this.createInput('time', tdValue);
            input.classList.add('data_table_is_open');
            this.tdElement.innerHTML = input.outerHTML;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorSelect extends DataTableEditorAbstract {

    editorName() {
        return 'select';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let selectData = this.dataTable.thElement.dataset.select_data.split(',');
            let selectOption = '';
            selectData.forEach(option => {
                if (tdValue === option) {
                    selectOption += `<option selected title="${option}" value="${option}">${option}</option>`
                } else {
                    selectOption += `<option title="${option}" value="${option}">${option}</option>`
                }
            });
            selectOption = "<select class=\"default-selector mg-b-plus-1 data_table_is_open\">" + selectOption + "</select>";
            this.tdElement.innerHTML = selectOption;
        }
    }

    closeEditor() {
        let inputValue = this.tdElement.querySelector('select')?.value;
        if (this.tdElement.querySelector('select')) {
            this.tdElement.querySelector('select')?.remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {

    }
}

class DataTabledEditorSelectMultiple extends DataTableEditorAbstract {

    editorName() {
        return 'select';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText?.split(',');
            let selectData = this.dataTable.thElement.dataset.select_data.split(',');
            let selectOption = '';
            selectData.forEach(option => {
                let selected = '';
                for (let i in tdValue) {
                    let item = tdValue[i];
                    if (item === option) {
                        selected = 'selected';
                        break;
                    }
                }
                selectOption += `<option ${selected} title="${option}" value="${option}">${option}</option>`
            });
            selectOption = "<select multiple class=\"default-selector mg-b-plus-1 data_table_is_open\">" + selectOption + "</select>";
            this.tdElement.innerHTML = selectOption;
        }
    }

    closeEditor() {
        let inputValue = this.tdElement.querySelector('select')?.value;
        let selectOptions = this.tdElement.querySelector('select')?.options;
        let allSelectedValue = [];
        if (selectOptions) {
            for (let k = 0; k < selectOptions.length; k++) {
                let option = selectOptions[k];
                if (option.selected) {
                    allSelectedValue.push(option.value || option.text);
                }
            }
            inputValue = allSelectedValue.join(',');
        }

        if (this.tdElement.querySelector('select')) {
            this.tdElement.querySelector('select')?.remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {

    }
}

class DataTableEditorTextArea extends DataTableEditorAbstract {

    editorName() {
        return 'textarea';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            let textArea = document.createElement('textarea');
            textArea.classList.add('data_table_is_open');
            textArea.defaultValue = tdValue;
            textArea.value = tdValue;
            this.tdElement.innerHTML = textArea.outerHTML;
        }
    }

    closeEditor() {
        let textArea = this.tdElement.querySelector('textarea');
        let inputValue = textArea?.value;
        if (textArea) {
            textArea?.remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {

    }

}

class DataTableEditorFeaturedLink extends DataTableEditorAbstract {

    editorName() {
        return 'tonics_media_featured_link';
    }

    openEditor() {
        if (this.hasTdElement) {
            let tdValue = this.tdElement.innerText;
            this.tdElement.innerHTML = `<div data-widget-form="true" class="position:relative data_table_is_open width:100% ${this.editorName()}">
                    <input data-widget-file-url="true" type="url" 
                    class="${this.editorName()}_input form-control input-checkout bg:white-one color:black border-width:default border:black" 
                    name="plugin_url" placeholder="URL Link" value="${tdValue}">
                    <div class="d:flex flex-gap:small flex-wrap:wrap">
                        <button type="button" class="tonics-featured-link text-align:center bg:transparent border:none color:white bg:pure-black border-width:default border:black padding:default
                        margin-top:0 cursor:pointer">Upload Link</button>
                        
                    </div>
                </div>`;
        }
    }

    closeEditor() {
        let linkInput = this.tdElement.querySelector('[data-widget-file-url="true"]');
        let inputValue = linkInput?.value;
        if (linkInput) {
            linkInput?.remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {
    }
}

let LICENSE_PRICES_MAP = new Map();

class DataTableEditorLicensePrices extends DataTableEditorAbstract {

    PRICE_SYMBOL = '$';

    constructor() {
        super();
    }

    getPriceMap() {
        return LICENSE_PRICES_MAP;
    }

    editorName() {
        return 'tonics_license';
    }

    openEditor() {
        if (this.hasTdElement && this.tdElement.querySelector('template')) {
            let tdValue = this.tdElement;
            let template = tdValue.querySelector('template');
            if (template) {
                let licenses = template.innerHTML.trim();
                if (licenses) {
                    licenses = JSON.parse(licenses);
                    let dClickTap = this.tdElement.querySelector('.d-click-tap');
                    let selectOption = '';
                    licenses.forEach(option => {
                        let name = option.name;
                        let price = this.PRICE_SYMBOL + option.price;
                        let uniqueID = option.unique_id;
                        let select = '';
                        if (dClickTap.dataset.license === uniqueID) {
                            select = 'selected';
                        }
                        selectOption += `<option data-license="${uniqueID}" data-price="${option.price}" ${select} title="${name}" value="${uniqueID}">${name} (${price})</option>`
                    });
                    selectOption = "<select class=\"default-selector mg-b-plus-1 data_table_is_open\">" + selectOption + "</select>";
                    if (dClickTap) {
                        dClickTap.innerHTML = selectOption;
                    }
                }
            }
        }
    }

    closeEditor() {
        let inputValue = this.tdElement.querySelector('select')?.value;
        let licenseUniqueID = inputValue;
        let selectOptions = this.tdElement.querySelector('select')?.options;

        let allSelectedValue = [];
        let selectedOption = null;
        if (selectOptions) {
            for (let k = 0; k < selectOptions.length; k++) {
                let option = selectOptions[k];
                if (option.selected) {
                    selectedOption = option;
                    allSelectedValue.push(option.text);
                    break;
                }
            }
            inputValue = allSelectedValue.join(',');
        }

        let dClickTap = this.tdElement.querySelector('.d-click-tap');
        if (dClickTap) {
            if (inputValue) {
                dClickTap.innerHTML = inputValue;
                dClickTap.dataset.license = licenseUniqueID;
                dClickTap.dataset.price = selectedOption?.dataset.price;
                dClickTap.dataset.buy = '1';
                this.getPriceMap().set(dClickTap.dataset.app, {
                    price: selectedOption?.dataset.price,
                    licenseUniqueID: licenseUniqueID
                });
                this.editorElement = null;
                if (this.tdElement.querySelector('select')) {
                    this.tdElement.querySelector('select')?.remove();
                }
            } else {
                dClickTap.dataset.buy = '0';
            }
        }
    }

    editorValidation() {
    }

    getPriceInfoForPaymentHandler() {
        const myObject = {};
        this.getPriceMap().forEach((value, key) => {
            myObject[key] = value;
        });
        return myObject
    }

    getTotalItemPrice() {
        // Calculate the total price using Array.from() and map
        return Array.from(this.getPriceMap().values()).reduce((acc, info) => acc + parseFloat(info.price), 0);
    }
}

//----------------
//--- EVENTS
//----------------
class OnBeforeScrollBottomEvent extends DataTableAbstractAndTarget {

}

class OnScrollBottomEvent extends DataTableAbstractAndTarget {

}

class OnClickEvent extends DataTableAbstractAndTarget {

}

class OnShiftClickEvent extends DataTableAbstractAndTarget {

}

class OnDoubleClickEvent extends DataTableAbstractAndTarget {

}

class OnRowMarkForDeletionEvent extends DataTableAbstractAndTarget {

}

//----------------
//--- HANDLERS
//----------------

class OpenEditorHandler {

    constructor(event) {
        let dataTable = event.dataTable,
            editingElementsCloneBeforeChanges = dataTable.editingElementsCloneBeforeChanges;

        if (dataTable.hasTrElement && editingElementsCloneBeforeChanges.has(dataTable.trElement.dataset.list_id) === false) {
            editingElementsCloneBeforeChanges.set(dataTable.trElement.dataset.list_id, dataTable.trElement.cloneNode(true));
        }

        if (event.getElementTarget().tagName.toLowerCase() === 'td' && dataTable.hasThElement) {
            event.getElementTarget().focus();
            let EditorsConfig = window?.TonicsDataTable?.Editors;
            let editorType = dataTable.thElement.dataset?.type.toUpperCase();
            if (EditorsConfig.has(editorType)) {
                let editorsClass = EditorsConfig.get(editorType);
                let editorsObject = new editorsClass;
                dataTable.tdElementChildBeforeOpen = event.getElementTarget().innerHTML
                editorsObject.tdElement = event.getElementTarget();
                editorsObject.dataTable = dataTable;
                editorsObject.openEditor();
                dataTable.currentEditor = editorsObject;
            }
        }
    }
}

class CloseEditorHandler {

    constructor(event) {
        let dataTable = event.dataTable;
        let currentEditor = dataTable.currentEditor;
        if (currentEditor instanceof DataTableEditorAbstract) {
            currentEditor.closeEditor();
            let tdElementChildBeforeOpen = null;
            if (dataTable.hasTdElement) {
                tdElementChildBeforeOpen = dataTable.tdElementChildBeforeOpen;
            }

            if (tdElementChildBeforeOpen !== null && tdElementChildBeforeOpen !== currentEditor.tdElement.innerHTML) {
                // For Single Edit
                currentEditor.tdElement.classList.add('editing');
                let trEl = currentEditor.tdElement.closest('tr');
                if (trEl?.dataset?.list_id) {
                    event.dataTable.editingElements.set(trEl.dataset.list_id, trEl);
                }

                // For Batch Editing
                if (dataTable.lockedSelection) {
                    let thEl = dataTable.findCorrespondingTableHeader(currentEditor.tdElement);
                    let allTdsElement = dataTable.getThElementColumns(thEl, dataTable.getAllSelectTableRow());
                    if (allTdsElement.length > 1) {
                        allTdsElement.forEach(td => {
                            let trEl = td.closest('tr');
                            if (dataTable.editingElementsCloneBeforeChanges.has(trEl.dataset.list_id) === false) {
                                dataTable.editingElementsCloneBeforeChanges.set(trEl.dataset.list_id, trEl.cloneNode(true));
                            }

                            td.innerHTML = currentEditor.tdElement.innerHTML;
                            td.classList.add('editing');
                            if (trEl?.dataset?.list_id) {
                                event.dataTable.editingElements.set(trEl.dataset.list_id, trEl);
                            }
                        });
                    }
                }
            }
        }
    }

}

class CanActivateCancelEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        if (dataTable.editingElements.size > 0 || dataTable.deletingElements.size > 0) {
            dataTable.activateMenus([dataTable.menuActions().CANCEL_EVENT]);
        } else {
            dataTable.deActivateMenus([dataTable.menuActions().CANCEL_EVENT]);
        }
    }
}

class CanActivateCopyFieldItemsEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        if (dataTable.hasTrElement) {
            dataTable.activateMenus([dataTable.menuActions().COPY_FIELD_ITEMS_EVENT]);
        } else {
            dataTable.deActivateMenus([dataTable.menuActions().COPY_FIELD_ITEMS_EVENT]);
        }
    }
}

class CanActivatePurchaseFieldItemsEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        if (dataTable.parentElement.dataset?.type === "TONICS_APP_STORE") {
            let buy = dataTable.parentElement.querySelectorAll('[data-buy]');
            if (buy && buy.length > 0) {
                dataTable.activateMenus([dataTable.menuActions().TONICS_APP_STORE_PURCHASE]);
                return;
            }
        }
        LICENSE_PRICES_MAP = new Map();
        dataTable.deActivateMenus([dataTable.menuActions().TONICS_APP_STORE_PURCHASE]);
    }
}

class CopyFieldItemsEventHandler {
    constructor(event) {
        let saveData = {
            type: [],
            headers: [],
            copyFieldItemsElements: [],
        };
        let dataTable = event.dataTable;
        let isCopyFieldItemsEventEvent = event.getElementTarget().closest(`[data-menu-action="CopyFieldItemsEvent"]`);
        if (isCopyFieldItemsEventEvent) {
            dataTable.collateTdFromTrAndPushToSaveTo(dataTable.getAllSelectTableRow(), saveData.copyFieldItemsElements, dataTable.getThHeaders());
            dataTable.activateMenus([dataTable.menuActions().COPY_FIELD_ITEMS_EVENT]);
            saveData.type.push(dataTable.apiEvents().COPY_FIELD_ITEMS_EVENT);
            dataTable.sendPostRequest(saveData, (data) => {
                if (data.status === 200) {
                    if (data?.more === dataTable.apiEvents().COPY_FIELD_ITEMS_EVENT) {
                        return copyToClipBoard(JSON.stringify(data?.data, null, "\t")).then(() => {
                            successToast(data.message);
                        }).catch(() => {
                            errorToast('Failed To Copy');
                        });
                    }
                }
            }, (err) => {
                let errMsg = err?.message ?? 'An error occurred copying field items';
                errorToast(errMsg);
            });
        }
    }
}

class CancelEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        let isCancelEvent = event.getElementTarget().closest(`[data-menu-action="CancelEvent"]`);
        if (isCancelEvent) {
            let allHighlight = dataTable.parentElement.querySelectorAll('.deleting');
            dataTable.deletingElements.clear();
            allHighlight.forEach(toDelete => {
                toDelete.classList.remove('deleting');
            });

            if (dataTable.editingElementsCloneBeforeChanges.size > 0) {
                dataTable.editingElementsCloneBeforeChanges.forEach(editing => {
                    let listID = editing.dataset.list_id;
                    let currentEdit = dataTable.parentElement.querySelector(`[data-list_id="${listID}"]`);
                    currentEdit.replaceWith(dataTable.editingElementsCloneBeforeChanges.get(listID));
                });
            }

            dataTable.resetEditingState();
        }
    }
}

class CanActivateSaveEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        if (dataTable.editingElements.size > 0 || dataTable.deletingElements.size > 0) {
            dataTable.activateMenus([dataTable.menuActions().SAVE_EVENT]);
        } else {
            dataTable.deActivateMenus([dataTable.menuActions().SAVE_EVENT]);
        }
    }
}

class SaveEventHandler {
    constructor(event) {
        let saveData = {
            type: [],
            headers: [],
            deleteElements: [],
            updateElements: [],
        };

        let dataTable = event.dataTable;
        let saveEvent = event.getElementTarget().closest(`[data-menu-action="SaveEvent"]`);
        if (saveEvent) {
            saveData.headers = dataTable.getThHeaders();

            if (dataTable.deletingElements.size > 0) {
                dataTable.collateTdFromTrAndPushToSaveTo(dataTable.deletingElements, saveData.deleteElements, saveData.headers);
                saveData.type.push(dataTable.apiEvents().DELETE_EVENT);
            } else if (dataTable.editingElements.size > 0) {
                dataTable.collateTdFromTrAndPushToSaveTo(dataTable.editingElements, saveData.updateElements, saveData.headers);
                saveData.type.push(dataTable.apiEvents().UPDATE_EVENT);
            }

            promptToast("Confirm Once Again, Before I Proceed", "Proceed", () => {
                dataTable.sendPostRequest(saveData, (data) => {
                    if (data.status === 200) {
                        if (data.more === dataTable.apiEvents().UPDATE_EVENT) {
                            dataTable.resetEditingElements();
                        }

                        if (data.more === dataTable.apiEvents().DELETE_EVENT) {
                            dataTable.removeDeletingElements();
                        }
                        successToast(data.message);
                    }
                }, (err) => {
                    let errMsg = err?.message ?? 'An error occurred saving changes';
                    errorToast(errMsg);
                });
            });

        }
    }
}

class ReloadEventHandler {
    constructor(event) {
        let reloadEvent = event.getElementTarget().closest(`[data-menu-action="ReloadEvent"]`);
        if (reloadEvent) {
            window.location.reload();
        }
    }
}

class MultiEditEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        let multiEditEvent = event.getElementTarget().closest(`[data-menu-action="MultiEditEvent"]`);
        if (multiEditEvent) {
            let lockedSpan = multiEditEvent.querySelector('.multi-edit-locked-mode');
            if (multiEditEvent.dataset.locked === 'false') {
                lockedSpan.innerText = '(Locked)';
                multiEditEvent.dataset.locked = 'true';
                dataTable.lockedSelection = true;
            } else {
                lockedSpan.innerText = '(UnLocked)';
                multiEditEvent.dataset.locked = 'false';
                dataTable.lockedSelection = false;
            }
        }
    }
}

class DeleteEventHandler {
    constructor(event) {
        let dataTable = event.dataTable;
        let isDeleteEvent = event.getElementTarget().closest(`[data-menu-action="DeleteEvent"]`);
        if (isDeleteEvent) {
            let allHighlight = dataTable.parentElement.querySelectorAll('.highlight');
            allHighlight.forEach(toDelete => {
                dataTable.deletingElements.set(toDelete.dataset.list_id, toDelete);
                toDelete.classList.add('deleting');
            });

            let OnRowMarkForDeletion = new OnRowMarkForDeletionEvent(event.getElementTarget(), dataTable);
            dataTable.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnRowMarkForDeletion, OnRowMarkForDeletionEvent);
        }
    }
}

//---------------------------
//--- HANDLER AND EVENT SETUP
//---------------------------
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(
        ...[
            CloseEditorHandler, CancelEventHandler, ReloadEventHandler, MultiEditEventHandler, SaveEventHandler,
            DeleteEventHandler, CanActivateSaveEventHandler, CanActivateCopyFieldItemsEventHandler, CanActivateCancelEventHandler,
            CanActivatePurchaseFieldItemsEventHandler, CopyFieldItemsEventHandler
        ]
    );
    window.TonicsEvent.EventConfig.OnRowMarkForDeletionEvent.push(
        ...[
            CanActivateCancelEventHandler,
            CanActivateSaveEventHandler
        ]
    );
    window.TonicsEvent.EventConfig.OnDoubleClickEvent.push(OpenEditorHandler);
}

//---------------------------
//--- Built-In Editor Setup
//---------------------------
window.TonicsDataTable = {};
window.TonicsDataTableClass = null;
window.TonicsDataTable.Editors = new Map();

window.TonicsDataTable.Editors.set('TEXT', DataTableEditorAbstract);
window.TonicsDataTable.Editors.set('TEXT_AREA', DataTableEditorTextArea);
window.TonicsDataTable.Editors.set('NUMBER', DataTabledEditorNumber);
window.TonicsDataTable.Editors.set('SELECT', DataTabledEditorSelect);
window.TonicsDataTable.Editors.set('SELECT_MULTIPLE', DataTabledEditorSelectMultiple);
window.TonicsDataTable.Editors.set('DATE', DataTabledEditorDate);
window.TonicsDataTable.Editors.set('DATE_TIME_LOCAL', DataTabledEditorDateLocal);
window.TonicsDataTable.Editors.set('DATE_MONTH', DataTabledEditorDateMonth);
window.TonicsDataTable.Editors.set('DATE_WEEK', DataTabledEditorDateWeek);
window.TonicsDataTable.Editors.set('DATE_TIME', DataTabledEditorDateTime);
window.TonicsDataTable.Editors.set('TONICS_MEDIA_FEATURE_LINK', DataTableEditorFeaturedLink);
window.TonicsDataTable.Editors.set('TONICS_LICENSE', DataTableEditorLicensePrices);

// boot dataTable
const dataTable = new DataTable('.dataTable');
dataTable.boot();
window.TonicsDataTableClass = dataTable;