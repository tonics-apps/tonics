
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
                message += text;
                const lastIndex = message.lastIndexOf('\n');
                if (lastIndex !== -1) {
                    const textToSend = message.substring(0, lastIndex);
                    message = message.substring(lastIndex + 1);
                    if (textToSend !== '') {
                        onMessage(textToSend);
                    }
                }
                return reader.read().then(processText);
            });
        })
        .catch(error => {
            onError(error);
        });
}


function handleOpenAIChat(event, fields, elementTarget) {
    const messagesCollection = [];
    fields.forEach((field) => {
        let fieldOptions = JSON.parse(field.field_options);
        if (fieldOptions?.app_tonicsai_openai_chat_message){
            messagesCollection.push({role: 'user', content: fieldOptions.app_tonicsai_openai_chat_message})
        }
    });

    const url = '/admin/tools/apps/tonics_ai/chat/completions';
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
            if (eventData === "[DONE]") {
                console.log("Done.");
                return;
            }
            try {
                const events = parseEventStream(eventData);
                events.forEach((event) => {
                    if (event){
                        const data = JSON.parse(event.data);
                        const jsonData = data.substring(6); // remove the "data: " prefix
                        const parsedData = JSON.parse(jsonData);
                        if (parsedData?.choices[0]?.delta?.content){
                            let txt = parsedData.choices[0].delta.content;
                            if (txt.includes('\n')) {
                                elementTarget.nextElementSibling.innerHTML += txt.replace(/\n/g, '<br>');
                            } else {
                                const textNode = document.createTextNode(txt);
                                elementTarget.nextElementSibling.appendChild(textNode);
                            }
                            // elementTarget.nextElementSibling.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        }
                    }
                });
            } catch (e) {

            }
        },
        function onError(error) {
            console.error(error);
        }
    );
}

function handleOpenAICompletion(event, fields, elementTarget) {
}

