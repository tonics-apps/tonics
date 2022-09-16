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

    constructor($parentElement) {
        this.parentElement = document.querySelector($parentElement)
        this.resetListID();
    }

    getParentElement() {
        return this.parentElement;
    }

    boot() {

        if (this.getParentElement()){
            // For Click Event
            if (!this.getParentElement().hasAttribute("data-event-click")) {
                this.getParentElement().setAttribute('data-event-click', 'true');
                this.getParentElement().addEventListener('click', (e) => {
                    let el = e.target;
                    let trEl = el.closest('tr');
                    if (e.shiftKey) {
                        this.resetPreviousTrState()
                        this.setShiftClick(trEl);
                        let Click = new OnShiftClickEvent(el, this);
                        Click.trElement = el.closest('tr');
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
                        Click.trElement = el.closest('tr');
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
                    OnDoubleClick.trElement = el.closest('tr');
                    OnDoubleClick.thElement = this.findCorrespondingTableHeader(el);
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

    get trElement() {
        return this._trElement;
    }

    set trElement(value) {
        this.hasTrElement = !!value; // True if value is not empty, otherwise, false
        this._trElement = value;
    }

    constructor(target, dataTableClass) {
        this._elementTarget = target;
        this._dataTable = dataTableClass;
    }

    getElementTarget() {
        return this._elementTarget;
    }

    getDataTable() {
        return this._dataTable;
    }

    getTrElement() {
        return this._trElement;
    }
}

class DataTableEditorAbstract {

    hasTdElement = false;

    get tdElement() {
        return this._tdElement;
    }

    set tdElement(value) {
        this.hasTdElement = !!value; // True if value is not empty, otherwise, false
        this._tdElement = value;
    }

    editorName() {
        return 'text';
    }

    openEditor() {
        return;
    }

    closeEditor() {
        return;
    }
}

window.TonicsDataTable = {};
window.TonicsDataTable.Editors = [];

class DataTabledEditorNumber extends DataTableEditorAbstract{
    editorName() {
        return 'number';
    }

    openEditor() {
        return super.openEditor();
    }

    closeEditor() {
        return super.closeEditor();
    }
}

window.TonicsDataTable.Editors.push(...[DataTableEditorAbstract, DataTabledEditorNumber]);

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

    hasThElement = false;

    get thElement() {
        return this._thElement;
    }

    set thElement(value) {
        this.hasThElement = !!value; // True if value is not empty, otherwise, false
        this._thElement = value;
    }

    getThElement() {
        return this._thElement;
    }
}

//----------------
//--- HANDLERS
//----------------

class BuiltInEditorHandler {

    constructor(event) {
        if (event.getElementTarget().tagName.toLowerCase() === 'td'){
            event.getElementTarget().setAttribute('contenteditable', 'true');
            event.getElementTarget().focus();
            console.log(event, event.getElementTarget())
        }
    }

}

class HandleRowHighlight {

}

// HANDLER AND EVENT SETUP
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(HandleRowHighlight);
    window.TonicsEvent.EventConfig.OnDoubleClickEvent.push(BuiltInEditorHandler);
}

// Remove This
const dataTable = new DataTable('.dataTable');
dataTable.boot();