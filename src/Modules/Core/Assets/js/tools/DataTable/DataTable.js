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

    constructor($parentElement) {
        this.parentElement = document.querySelector($parentElement)
    }

    getParentElement() {
        return this.parentElement;
    }

    boot() {
        // For Click Event
        if (this.getParentElement() && !this.getParentElement().hasAttribute("data-event-click")) {
            this.getParentElement().setAttribute('data-event-click', 'true');
            this.getParentElement().addEventListener('click', (e) => {
                let el = e.target;
                let Click = new OnClickEvent(el, this);
                Click.trElement = el.closest('tr');
                this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, Click, OnClickEvent);
            });
        }

        // For Double-Click Event
        if (this.getParentElement() && !this.getParentElement().hasAttribute("data-event-dblclick")) {
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
        if (this.getParentElement() && !this.getParentElement().hasAttribute("data-event-scroll-bottom")) {
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
        let tableRows = document.querySelector(this.parentElement).querySelectorAll('tr');

    }
}

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

//----------------
//--- EVENTS
//----------------
class OnBeforeScrollBottomEvent extends DataTableAbstractAndTarget {

}

class OnScrollBottomEvent extends DataTableAbstractAndTarget {

}

class OnClickEvent extends DataTableAbstractAndTarget {

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

class HandleRowHighlight {
    constructor(event) {
        if (event.hasTrElement && event.getElementTarget().dataset.hasOwnProperty('checkbox_select')) {
            let trElement = event.getTrElement();
            trElement.classList.toggle('highlight');
        }
    }
}

// HANDLER AND EVENT SETUP
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(HandleRowHighlight);
}