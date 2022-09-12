
const trashButtons = document.querySelectorAll('[data-click-onconfirmtrash="true"]');
const deleteButtons = document.querySelectorAll('[data-click-onconfirmdelete="true"]');

if (trashButtons) {
    let trashIsBusy = false;
    trashButtons.forEach((value, key) => {
        value.addEventListener('click', (e) => {
            let button = e.target;
            if (trashIsBusy === false) {
                myModule.promptToast("Do you want to Move Item To Trash?", "Move To Trash", () => {
                    trashIsBusy = true;
                    button.type = 'submit'
                    button.click();
                    trashIsBusy = false;
                })
            }
        })
    });
}

if (deleteButtons) {
    let trashIsBusy = false;
    deleteButtons.forEach((value, key) => {
        value.addEventListener('click', (e) => {
            let button = e.target;
            if (trashIsBusy === false) {
                myModule.promptToast("Do you want to Delete Item?", "Delete Item", () => {
                    trashIsBusy = true;
                    button.type = 'submit'
                    button.click();
                    trashIsBusy = false;
                })
            }
        })
    });
}