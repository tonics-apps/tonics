
class OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

class OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

if (window?.parent.TonicsEvent?.EventConfig){
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldPreviewEvent.push(OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc);
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldSubmitEvent.push(OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc);
}

function handleTonicsToc(event, target) {
    if (target.closest('.tabs').querySelector("input[value='Apps_TonicsToc']")) {
        const TocEditor = window.TonicsScript.TableOfContent('.entry-content');
        TocEditor.tocDepth(6).run();
        const headersFound = TocEditor.$tableOfContentDetails.noOfHeadersFound;
        const Tree = TocEditor.tocTree
        event._postData.tableOfContentData = {
            'headersFound': headersFound,
            'tree': Tree
        };
    }
}