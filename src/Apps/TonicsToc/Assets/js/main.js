
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
    if (target.closest('.tabs')) {
        const TocEditor = window.TonicsScript.TableOfContent('.entry-content');
        TocEditor.tocDepth(6).run();
        const headersFound = TocEditor.$tableOfContentDetails.noOfHeadersFound;
        const Tree = TocEditor.tocTree
        let fields = JSON.parse(event._postData);
        for (const i in fields) {
            let field = fields[i];
            if (field.hasOwnProperty('main_field_slug') && field.main_field_slug === 'app-tonicstoc'){
                if (!field.field_options?.tableOfContentData){
                    field.field_options = JSON.parse(field.field_options);
                }
                field.field_options.tableOfContentData = {
                    'headersFound': headersFound,
                    'tree': Tree
                };
            }
        }
        event._postData = JSON.stringify(fields);
    }
}