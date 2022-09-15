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

    constructor($parentElement) {
        this.parentElement = $parentElement
    }

    boot() {
        let parentEl = document.querySelector(this.parentElement);
        // For Click Event
        if (parentEl && !parentEl.hasAttribute("data-event-click")){
            parentEl.setAttribute('data-event-click', 'true');
            parentEl.addEventListener('click', (e) => {
                console.log(e.target);
            });
        }

        // For Double-Click Event
        if (parentEl && !parentEl.hasAttribute("data-event-dblclick")){
            parentEl.setAttribute('data-event-dblclick', 'true');
            parentEl.addEventListener('dblclick', (e) => {
                console.log(e.target);
            });
        }

        // For Scroll Bottom
        if (parentEl && !parentEl.hasAttribute("data-event-scroll-bottom")){
            parentEl.setAttribute('data-event-scroll-bottom', 'true');
            parentEl.addEventListener('scroll', (e) => {
                let el = e.target;
                let heightTop = el.scrollHeight - el.scrollTop;
                // the 400 gives us time to react quickly that the scroll is almost/at the bottom
                let clientHeight = el.clientHeight + 500;
                console.log(heightTop, clientHeight);

                // almost at the bottom
                if (heightTop < clientHeight){
                    console.log('all most at bottom');
                }

                // at the bottom
                if (heightTop === el.clientHeight){
                    let OnBeforeTonicsFieldSubmit = new OnScrollBottomEvent(el);
                    this.getEventDispatcher().dispatchEventToHandlers()
                    console.log(window.TonicsEvent.EventDispatcher, el)
                    console.log('at the bottom');
                }
            });
        }
    }

    getEventDispatcher() {
        return window.TonicsEvent.EventDispatcher;
    }
}

//--- EVENTS
class OnBeforeScrollBottomEvent {
    get elementTarget() {
        return this._elementTarget;
    }

    set elementTarget(value) {
        this._elementTarget = value;
    }

    constructor(target) {
        this._elementTarget = target;
    }

    getElementTarget() {
        return this._elementTarget;
    }
}

class OnScrollBottomEvent {
    get elementTarget() {
        return this._elementTarget;
    }

    set elementTarget(value) {
        this._elementTarget = value;
    }

    constructor(target) {
        this._elementTarget = target;
    }

    getElementTarget() {
        return this._elementTarget;
    }
}


// On Double-Click, Handle Editors Mode
class HandleEditorsMode {

}