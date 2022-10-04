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
        let dataTdOfTType = null;
        let trEl = event.elementTarget.closest('tr');
        if ((dataTdOfTType = trEl.querySelector('[data-td="type"]'))){
            if (dataTdOfTType.innerText === 'MODULE'){
                dataTable.deActivateMenus([dataTable.menuActions().DELETE_EVENT]);
            } else {
                dataTable.activateMenus([dataTable.menuActions().DELETE_EVENT]);
            }
        }
    }

}

if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(
        ...[
            DisableDeleteMenuOnModuleAppSelection
        ]
    );
}