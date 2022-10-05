/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

class DisableDeleteMenuOnModuleAppSelection {

    constructor(event) {
        let dataTable = event.dataTable;
        let dataTdOfTType;
        let trEl = event.elementTarget.closest('tr');
        if ((dataTdOfTType = trEl?.querySelector('[data-td="type"]'))){
            if (dataTdOfTType.innerText.toLowerCase() === 'module'){
                dataTable.deActivateMenus([dataTable.menuActions().DELETE_EVENT]);
            } else {
                dataTable.activateMenus([dataTable.menuActions().DELETE_EVENT]);
            }
        }
    }
}

class DisableUpdateMenuOnNonUpdateAvailability {

    constructor(event) {
        let dataTable = event.dataTable;
        let dataTdOfTType = null;
        let trEl = event.elementTarget.closest('tr');
        if ((dataTdOfTType = trEl?.querySelector('[data-td="update_available"]'))){
            if (dataTdOfTType.innerText.toLowerCase() === 'no'){
                dataTable.deActivateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
            } else {
                dataTable.activateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
            }
        }
    }

}

class UpdateEventHandlerForApps {
    constructor(event) {
        let appUpdateData = {
            type: [],
            headers: [],
            appUpdateElements: [],
        }
        let dataTable = event.dataTable,
            updateEvent = event.getElementTarget().closest(`[data-menu-action="AppUpdateEvent"]`),
            headers = [];
        if (updateEvent) {
            dataTable.getAllThElements().forEach(header => {
                headers.push(header.dataset?.slug)
            });

            appUpdateData.headers = headers;
            let getAllSelectedTrElement = dataTable.getAllSelectedTrElement();
            if (getAllSelectedTrElement.length > 0){
                dataTable.collateTdFromTrAndPushToSaveTo(getAllSelectedTrElement, appUpdateData.appUpdateElements, appUpdateData.headers);
                appUpdateData.type.push(dataTable.apiEvents().APP_UPDATE_EVENT);

                window.TonicsScript.promptToast("Update Operation Might Be Irreversible", "Proceed To Update", () => {
                    updateEvent.querySelector('.loading-animation').classList.remove('d:none');
                    dataTable.deActivateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
                    dataTable.sendPostRequest(appUpdateData, (data) => {
                        updateEvent.querySelector('.loading-animation').classList.add('d:none');
                        if (data.status === 200){
                            window.TonicsScript.successToast(data.message);
                        }
                    }, (err) => {
                        let errMsg = err?.message ?? 'An error occurred updating apps';
                        updateEvent.querySelector('.loading-animation').classList.add('d:none');
                        window.TonicsScript.errorToast(errMsg);
                    });
                });
            }
        }

    }
}

if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(
        ...[
            DisableDeleteMenuOnModuleAppSelection,
            DisableUpdateMenuOnNonUpdateAvailability,
            UpdateEventHandlerForApps,
        ]
    );
}