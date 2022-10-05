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
                dataTable.deActivateMenus([dataTable.menuActions().UPDATE_EVENT]);
            } else {
                dataTable.activateMenus([dataTable.menuActions().UPDATE_EVENT]);
            }
        }
    }

}

class UpdateEventHandlerForApps {
    constructor(event) {
        let updateEvent = event.getElementTarget().closest(`[data-menu-action="AppUpdateEvent"]`);
        if (updateEvent) {
            console.log(updateEvent);
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