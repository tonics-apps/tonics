
try {
    let tonicsFlashMessages = document.querySelector('body')?.getAttribute('data-tonics_flashMessages');
    function flattenTonicsFlashMessagesArray(messages) {
        const flattened = [];
        function flatten(value) {
            if (Array.isArray(value)) {
                value.forEach(flatten);
            } else if (typeof value === 'object' && value !== null) {
                Object.values(value).forEach(flatten);
            } else {
                flattened.push(value);
            }
        }
        flatten(messages);
        return flattened;
    }

    if (tonicsFlashMessages) {
        tonicsFlashMessages = JSON.parse(tonicsFlashMessages);
        if (tonicsFlashMessages.hasOwnProperty('successMessage')) {
            flattenTonicsFlashMessagesArray(tonicsFlashMessages.successMessage).forEach((value) => {
                successToast(value, 10000);
            });
        }

        if (tonicsFlashMessages.hasOwnProperty('errorMessage')) {
            flattenTonicsFlashMessagesArray(tonicsFlashMessages.errorMessage).forEach((value) => {
                errorToast(value, 10000);
            });
        }

        if (tonicsFlashMessages.hasOwnProperty('infoMessage')) {
            flattenTonicsFlashMessagesArray(tonicsFlashMessages.infoMessage).forEach((value) => {
                infoToast(value, 10000);
            });
        }
    }

} catch (e) {
    console.log(e.toLocaleString());
}