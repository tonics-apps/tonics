
class OnBeforeTonicsFieldPreviewEventHandlerForTonicsAI {
    constructor(event) {
        let elementTarget = event._elementTarget
        let fields = JSON.parse(event._postData);
        let modularRow = fields[0];
        if (modularRow.hasOwnProperty('main_field_slug')){
            // set canRequest to true if field_slug startsWith('...'), else false
            event._canRequest = !modularRow.main_field_slug.startsWith('app-tonicsai');
            if (modularRow.main_field_slug.startsWith('app-tonicsai')){
                // If tonics_selected is equals 1, then it means the user selected the tabsContainer body,
                // which automatically closes the preview before clicking the preview again, so, when user does that
                // we assume they just wanna get back to the preview content, so, we return if and only if the target innerText is not empty
                // however, clicking preview again would do a new chatting
                let tabContainer = elementTarget.closest('.tabs');
                if (tabContainer.dataset?.tonics_selected === '1'){
                    if (elementTarget.nextElementSibling.innerText.trim() !== '') {
                        tabContainer.dataset.tonics_selected = '0';
                        return;
                    }
                }
                event.loadAnimation(elementTarget);
                // Handlers
                if (modularRow.main_field_slug === 'app-tonicsai-openai-chat'){
                    handleOpenAIChat(event, fields, elementTarget);
                }

                if (modularRow.main_field_slug === 'app-tonicsai-openai-image'){
                    handleOpenAIImage(event, fields, elementTarget);
                }

                if (modularRow.main_field_slug === 'app-tonicsai-openai-completion'){
                    handleOpenAICompletion(event, fields, elementTarget);
                }
            }
        }
    }
}

if (window?.parent.TonicsEvent?.EventConfig){
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldPreviewEvent.push(OnBeforeTonicsFieldPreviewEventHandlerForTonicsAI);
}

function parseEventStream(stream) {
    const chunks = stream.split(/\n\n/);
    const events = [];

    chunks.forEach(function(chunk) {
        const e = parseEventChunk(chunk);
        if (e) {
            events.push(e);
        }
    });

    return events;
}

function parseEventChunk(chunk) {
    if (!chunk || chunk.length === 0) {
        return null;
    }

    const e = {'id': null, 'retry': null, 'data': '', 'event': 'message'};
    chunk.split(/\n|\r\n|\r/).forEach(function(line) {
        line = line.trimStart();
        const index = line.indexOf(':');
        if (index <= 0) {
            // Line was either empty, or started with a separator and is a comment.
            // Either way, ignore.
            return;
        }

        const field = line.substring(0, index);
        if (!(field in e)) {
            return;
        }

        const value = line.substring(index + 1).trimStart();
        if (field === 'data') {
            e[field] += value;
        } else {
            e[field] = value;
        }
    });

    return e;
}

function createEventStream(url, options = {}, onResponseOkay = () => {}, onMessage = () => {}, onError = () => {}) {
    const { headers = {}, body = {} } = options;

    fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(body),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to open event stream.');
            }
            onResponseOkay(response);
            const reader = response.body.getReader();
            let message = '';
            reader.read().then(function processText({ done, value }) {
                if (done) {
                    onMessage("[DONE]");
                    return;
                }
                const text = new TextDecoder('utf-8').decode(value);
                const lines = (message + text).split('\n');
                for (let i = 0; i < lines.length - 1; i++) {
                    const line = lines[i];
                    if (line !== '') {
                        onMessage(line);
                    }
                }
                message = lines[lines.length - 1];
                return reader.read().then(processText);
            });
        })
        .catch(error => {
            onError(error);
        });
}


function handleOpenAIChat(event, fields, elementTarget) {

    const url = '/admin/tools/apps/tonics_ai/open_ai/chat/completions';
    const messagesCollection = [];
    fields.forEach((field) => {
        let fieldOptions = JSON.parse(field.field_options);
        if (fieldOptions?.app_tonicsai_openai_chat_message){
            messagesCollection.push({role: 'user', content: fieldOptions.app_tonicsai_openai_chat_message})
        }
    });

    createEventStream(
        url,
        {
            headers: {
                'Tonics-CSRF-Token': `${event.getCSFRToken(['tonics_csrf_token', 'csrf_token', 'token'])}`,
                'Content-Type': 'application/json'
            },
            body: {
                model: 'YOUR_MODEL_NAME',
                messages: messagesCollection,
                temperature: 1,
                top_p: 1,
                max_tokens: 2000,
                stream: true
            }
        },
        function (response) {
            elementTarget.nextElementSibling.innerHTML = '';
        },
        function onMessage(eventData) {
            let tabContainer = elementTarget.closest('.tabs');
            if (eventData === "[DONE]") {
                tabContainer.dataset.tonics_selected = '0'; // release user to send new request
                console.log("Done.");
                return;
            }
            try {
                const events = parseEventStream(eventData);
                events.forEach((event) => {
                    if (event && event?.event){
                        if (event.event === 'message'){
                            const data = JSON.parse(event.data);
                            const jsonData = data.substring(6); // remove the "data: " prefix
                            const parsedData = JSON.parse(jsonData);
                            if (parsedData?.choices[0]?.delta?.content){
                                tabContainer.dataset.tonics_selected = '1'; // hold user from resending any new request
                                let txt = parsedData.choices[0].delta.content;
                                if (txt.includes('\n')) {
                                    elementTarget.nextElementSibling.innerHTML += txt.replace(/\n/g, '<br>');
                                } else {
                                    const textNode = document.createTextNode(txt);
                                    elementTarget.nextElementSibling.appendChild(textNode);
                                }
                                // elementTarget.nextElementSibling.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
                            }
                        } else if(event.event === 'issue') {
                            tabContainer.dataset.tonics_selected = '0'; // release user to send new request
                            const data = JSON.parse(event.data);
                            tonicsAIErrorMessage(elementTarget, data);
                        }
                    }
                });
            } catch (e) {
                tabContainer.dataset.tonics_selected = '0'; // release user to send new request
            }
        },
        function onError(error) {
            console.error(error);
        }
    );
}

function handleOpenAIImage(event, fields, elementTarget) {
    const url = '/admin/tools/apps/tonics_ai/open_ai/image';
    const body = {};
    fields.forEach((field) => {
        let fieldOptions = JSON.parse(field.field_options);
        if (fieldOptions?.app_tonicsai_openai_image_message){
            body.prompt = fieldOptions.app_tonicsai_openai_image_message;
        }
        if (fieldOptions?.app_tonicsai_openai_image_nToGenerate){
            body.numberOfImagesToGenerate = fieldOptions.app_tonicsai_openai_image_nToGenerate;
        }
        if (fieldOptions?.app_tonicsai_openai_image_size){
            body.size = fieldOptions.app_tonicsai_openai_image_size;
        }
    });
    let defaultHeader = {
        'Tonics-CSRF-Token': `${event.getCSFRToken(['tonics_csrf_token', 'csrf_token', 'token'])}`
    };

    window.TonicsScript.XHRApi({...defaultHeader}).Post(url, JSON.stringify(body), function (err, data) {
        const GenericMessage = "An Error Occurred Generating Image, Try Again";
        try {
            if (data) {
                data = JSON.parse(data);
                if (data?.status !== 200){
                    tonicsAIErrorMessage(elementTarget, data?.message);
                } else {
                    elementTarget.nextElementSibling.innerHTML = data?.data;
                }
            } else {
                tonicsAIErrorMessage(elementTarget, GenericMessage);
            }
        } catch (e) {
            tonicsAIErrorMessage(elementTarget, e.message);
        }

    });
}

function handleOpenAICompletion(event, fields, elementTarget) {

}

function tonicsAIErrorMessage(elementTarget, message = '') {
    elementTarget.nextElementSibling.innerHTML = `<span class="color:red">${message}</span>`;
}

