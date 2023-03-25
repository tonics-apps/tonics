
class OnBeforeTonicsFieldPreviewEventHandlerForTonicsAI {
    constructor(event) {
        let elementTarget = event._elementTarget
        let fields = JSON.parse(event._postData);
        let modularRow = fields[0];
        if (modularRow.hasOwnProperty('main_field_slug')){
            // set canRequest to true if field_slug startsWith('...'), else false
            event._canRequest = !modularRow.main_field_slug.startsWith('app-tonicsai');
            if (modularRow.main_field_slug === 'app-tonicsai-openai-chat'){
                handleOpenAIChat(event, fields, elementTarget);
            }

            if (modularRow.main_field_slug === 'app-tonicsai-openai-completion'){
                handleOpenAICompletion(event, fields, elementTarget);
            }
        }
    }
}

if (window?.parent.TonicsEvent?.EventConfig){
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldPreviewEvent.push(OnBeforeTonicsFieldPreviewEventHandlerForTonicsAI);
}

function handleOpenAIChat(event, fields, elementTarget) {

}

function handleOpenAICompletion(event, fields, elementTarget) {
}

