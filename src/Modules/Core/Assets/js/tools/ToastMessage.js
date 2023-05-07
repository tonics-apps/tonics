
try {
    let tonicsFlashMessages = document.querySelector('body')?.getAttribute('data-tonics_flashMessages');
    if (tonicsFlashMessages) {
        tonicsFlashMessages = JSON.parse(tonicsFlashMessages);
        if (tonicsFlashMessages.hasOwnProperty('successMessage')) {
            tonicsFlashMessages.successMessage.forEach((value) => {
                successToast(value, 6000);
            });
        }

        if (tonicsFlashMessages.hasOwnProperty('errorMessage')) {
            tonicsFlashMessages.errorMessage.forEach((value) => {
                errorToast(value, 6000);
            });
        }
        if (tonicsFlashMessages.hasOwnProperty('infoMessage')) {
            tonicsFlashMessages.infoMessage.forEach((value) => {
                infoToast(value, 6000);
            });
        }
    }

} catch (e) {
    // console.log(e.toLocaleString());
}