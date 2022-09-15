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
        this.parentElement = $parentElement
    }

    boot() {
        let parentEl = document.querySelector(this.parentElement);
        // For Click Event
        if (parentEl && !parentEl.hasAttribute("data-event-click")){
            parentEl.setAttribute('data-event-click', 'true');
            parentEl.addEventListener('click', (e) => {
                let el = e.target;
                let Click = new OnClickEvent(el, this);
                this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, Click, OnClickEvent);
            });
        }

        // For Double-Click Event
        if (parentEl && !parentEl.hasAttribute("data-event-dblclick")){
            parentEl.setAttribute('data-event-dblclick', 'true');
            parentEl.addEventListener('dblclick', (e) => {
                let el = e.target;
                let OnDoubleClick = new OnDoubleClickEvent(el, this);
                console.log(this.findCorrespondingTableHeader(el))
                this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnDoubleClick, OnDoubleClickEvent);
            });
        }

        // For Scroll Bottom
        if (parentEl && !parentEl.hasAttribute("data-event-scroll-bottom")){
            parentEl.setAttribute('data-event-scroll-bottom', 'true');
            parentEl.addEventListener('scroll', (e) => {
                let el = e.target;
                let scrollDownwards = el.scrollHeight - el.scrollTop;
                // the 400 gives us time to react quickly that the scroll is almost/at the bottom
                let clientHeight = el.clientHeight + 500;

                // almost at the bottom
                if (scrollDownwards < clientHeight){
                    ++this.scrollToBottomLockPing;
                    if (this.scrollToBottomLockPing === 1){
                        let OnBeforeScrollBottom = new OnBeforeScrollBottomEvent(el, this);
                        this.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnBeforeScrollBottom, OnBeforeScrollBottomEvent);
                    }
                }

                // at the bottom
                if (scrollDownwards === el.clientHeight){
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
        for(i = 0; i < thCells.length; i++ ){
            th_colSpan_acc += thCells[i].colSpan
            if( th_colSpan_acc >= (idx + tdNode.colSpan) ) break
        }

        return thCells[i]
    }
}

class DataTableAbstractAndTarget {
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

    getDataTable() {
        return this._dataTable;
    }
}

//--- EVENTS
class OnBeforeScrollBottomEvent extends DataTableAbstractAndTarget{

}

class OnScrollBottomEvent extends DataTableAbstractAndTarget {

}

class OnClickEvent extends DataTableAbstractAndTarget{

}

class OnDoubleClickEvent extends DataTableAbstractAndTarget{

}


// On Double-Click, Handle Editors Mode
class HandleEditorsMode {

}