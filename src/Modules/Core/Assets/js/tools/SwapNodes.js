
export function swapNodes(el1, el2, el1InitialRect, onSwapDone = null) {
    let x1, y1, x2, y2;

    x1 = el1InitialRect.left - el2.getBoundingClientRect().left;
    y1 = el1InitialRect.top - el2.getBoundingClientRect().top;

    x2 = el2.getBoundingClientRect().left - el1InitialRect.left;
    y2 = el2.getBoundingClientRect().top - el1InitialRect.top;

    el1.classList.add('draggable-transition');
    el2.classList.add('draggable-transition');

    el2.style.transform = "translate(" + x1 + "px," + y1 + "px)";
    el1.style.transform = "translate(" + x2 + "px," + y2 + "px)";

    function swap(){
        el1.classList.remove('draggable-transition');
        el2.classList.remove('draggable-transition');

        el1.removeAttribute('style');
        el2.removeAttribute('style');

        let tempEl = document.createElement("div");
        el1.parentNode.insertBefore(tempEl, el1); el2.parentNode.insertBefore(el1, el2);
        tempEl.parentNode.insertBefore(el2, tempEl); tempEl.parentNode.removeChild(tempEl);
/*
        // THIS ONE KEEP LOSING SELECT DATA BUT THE TEMP VERSION ABOVE WORKS SUPERB
        let copyEl1 = el1.cloneNode(true);
        let copyEl2 = el2.cloneNode(true);
        el1.replaceWith(copyEl2);
        el2.replaceWith(copyEl1);*/
    }

    el2.addEventListener("transitionend", () => {
        swap();
        if (onSwapDone){
            onSwapDone();
        }
    }, { once: true });
}

if (!window.hasOwnProperty('TonicsScript')){ window.TonicsScript = {};}
window.TonicsScript.swapNodes = (el1, el2, el1InitialRect, onSwapDone = null) => swapNodes(el1, el2, el1InitialRect, onSwapDone);