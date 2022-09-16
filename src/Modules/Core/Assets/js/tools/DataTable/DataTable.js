/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

class DataTable {

    parentElement = '';
    scrollToBottomLockPing = 0;
    shiftClick = new Map();
    currentEditor = null;
    hasThElement = false;
    hasTdElement = false;
    hasTrElement = false;

    constructor($parentElement) {
        this.parentElement = document.querySelector($parentElement)
        this.resetListID();
    }

    getParentElement() {
        return this.parentElement;
    }

    get thElement() {
        return this._thElement;
    }

    set thElement(value) {
        this.hasThElement = !!value; // True if value is not empty, otherwise, false
        this._thElement = value;
    }

    get tdElement() {
        return this._trElement;
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

    boot() {

        if (this.getParentElement()){
            // For Click Event
            if (!this.getParentElement().hasAttribute("data-event-click")) {
                this.getParentElement().setAttribute('data-event-click', 'true');
                this.getParentElement().addEventListener('click', (e) => {
                    let el = e.target;
                    let trEl = el.closest('tr');
                    this.trElement = trEl;
                    this.tdElement = el.closest('td');

                    let isInput = el.closest('input, textarea, select');
                    if (isInput){
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
                        // this is a norm mouse click
                        this.resetPreviousTrState();
                        this.highlightTr(trEl);

                        // for shift key
                        this.resetShiftClick();
                        this.setShiftClick(trEl);

                        let Click = new OnClickEvent(el, this);
                        this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, Click, OnClickEvent);

                    }
                });
            }

            // For Double-Click Event
            if (!this.getParentElement().hasAttribute("data-event-dblclick")) {
                this.getParentElement().setAttribute('data-event-dblclick', 'true');
                this.getParentElement().addEventListener('dblclick', (e) => {
                    let el = e.target;
                    let OnDoubleClick = new OnDoubleClickEvent(el, this);
                    this.trElement = el.closest('tr');
                    this.tdElement = el.closest('td');
                    this.thElement = this.findCorrespondingTableHeader(el);
                    this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnDoubleClick, OnDoubleClickEvent);
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

    getSelectedTrElement () {
        return this.getParentElement().querySelector('.highlight');
    }

    getEventDispatcher() {
        return window.TonicsEvent.EventDispatcher;
    }

    getCurrentEditor() {
        return this.currentEditor;
    }

    /**
     * Credit: https://stackoverflow.com/a/46139306 @ https://stackoverflow.com/users/104380/vsync
     * @param tdNode
     * @returns {HTMLTableCellElement}
     */
    findCorrespondingTableHeader(tdNode) {
        let i;
        let idx = [...tdNode.parentNode.children].indexOf(tdNode), // get td index
            thCells = tdNode.closest('table').tHead.rows[0].cells, // get all th cells
            th_colSpan_acc = 0; // accumulator

        // iterate all th cells and add-up their colSpan value
        for (i = 0; i < thCells.length; i++) {
            th_colSpan_acc += thCells[i].colSpan
            if (th_colSpan_acc >= (idx + tdNode.colSpan)) break
        }

        return thCells[i]
    }

    resetListID() {
        let tableRows = this.getParentElement().querySelectorAll('tbody > tr');
        if (tableRows && tableRows.length > 0){
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
        let checkBox = trEl.querySelector('[data-checkbox_select]');
        if (checkBox){
            checkBox.setAttribute('checked', 'false');
        }
        trEl.classList.remove('highlight');
    }

    highlightTr(trEl) {
        let checkBox = trEl.querySelector('[data-checkbox_select]');
        if (checkBox){
            checkBox.setAttribute('checked', 'true');
        }
        trEl.classList.add('highlight');
    }

    resetShiftClick() {
        this.shiftClick = new Map();
    }

    setShiftClick(trEl) {
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
            listIDToLoop.sort();

            // loop over the sorted ranges. and highlight 'em
            for (let i = listIDToLoop[0]; i <= listIDToLoop[1]; i++) {
                // highlight file
                let trEl = this.parentElement.querySelector(`[data-list_id="${i}"]`);
                if (trEl) {
                    this.highlightTr(trEl);
                }
            }
        }
    }
}

//----------------
//--- ABSTRACT CLASSES
//----------------
class DataTableAbstractAndTarget {

    hasTrElement = false;
    hasTdElement = false;

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

    constructor(target, dataTableClass) {
        this._elementTarget = target;
        this._dataTable = dataTableClass;
    }

    getElementTarget() {
        return this._elementTarget;
    }
}

class DataTableEditorAbstract {

    hasTdElement = false;
    editorElement = null;

    /**
     * Create an input element
     * @param type
     * @param value
     * @returns {HTMLInputElement}
     */
    createInput(type = 'text', value = '') {
        let input = document.createElement('input');
        input.type = type;
        input.defaultValue = value;
        input.value = value;
        return input;
    }

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

    editorName() {
        return 'text';
    }

    openEditor() {
        if (this.hasTdElement){
            let tdValue = this.tdElement.innerText;
            this.editorElement = this.createInput('text', tdValue);
            this.tdElement.innerHTML = this.editorElement.outerHTML;
        }
    }

    closeEditor() {
        if (this.hasTdElement && this.tdElement.querySelector('input')){
            let inputValue = this.tdElement.querySelector('input').value;
            this.tdElement.querySelector('input').remove();
            this.tdElement.innerHTML = inputValue;
            this.editorElement = null;
        }
    }

    editorValidation() {

    }
}

window.TonicsDataTable = {};
window.TonicsDataTable.Editors = new Map();

class DataTabledEditorNumber extends DataTableEditorAbstract{
    editorName() {
        return 'number';
    }

    openEditor() {
        if (this.hasTdElement){
            let tdValue = this.tdElement.innerText;
            this.tdElement.innerHTML = this.createInput('number', tdValue).outerHTML;
        }
    }

    closeEditor() {
        return super.closeEditor();
    }

    editorValidation() {

    }
}

class DataTabledEditorSelect extends DataTableEditorAbstract{

    editorName() {
        return 'select';
    }

    openEditor() {
        if (this.hasTdElement){
            let tdValue = this.tdElement.dataset.select_data;
            console.log(tdValue, this);
           // this.tdElement.innerHTML = this.createInput('number', tdValue).outerHTML;
        }
    }

    closeEditor() {
        // return super.closeEditor();
    }

    editorValidation() {

    }
}

window.TonicsDataTable.Editors.set('TEXT', DataTableEditorAbstract);
window.TonicsDataTable.Editors.set('NUMBER', DataTabledEditorNumber);
window.TonicsDataTable.Editors.set('SELECT', DataTabledEditorSelect);

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

//----------------
//--- HANDLERS
//----------------

class OpenEditorHandler {

    constructor(event) {
        if (event.getElementTarget().tagName.toLowerCase() === 'td' && event.dataTable.hasThElement){
            event.getElementTarget().focus();
            let EditorsConfig = window?.TonicsDataTable?.Editors;
            let editorType = event.dataTable.thElement.dataset?.type.toUpperCase();
            if (EditorsConfig.has(editorType)){
                let editorsClass = EditorsConfig.get(editorType);
                let editorsObject = new editorsClass;
                editorsObject.tdElement = event.getElementTarget();
                editorsObject.dataTable = event.dataTable;
                editorsObject.openEditor();
                event.dataTable.currentEditor = editorsObject;
            }
        }
    }

}

class CloseEditorHandler {

    constructor(event) {
        let currentEditor = event.dataTable.currentEditor;
        if (currentEditor instanceof DataTableEditorAbstract){
           currentEditor.closeEditor();
        }
    }

}

// HANDLER AND EVENT SETUP
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(CloseEditorHandler);
    window.TonicsEvent.EventConfig.OnDoubleClickEvent.push(OpenEditorHandler);
}

// Remove This
const dataTable = new DataTable('.dataTable');
dataTable.boot();