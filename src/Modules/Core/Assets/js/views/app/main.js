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
        let trElement = null;
        let correspondingHeader = null;
        let thEl = dataTable.findCorrespondingTableHeader(event.elementTarget);
        console.log(event, event.elementTarget.closest('tr'));
    }

}

if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnClickEvent.push(
        ...[
            DisableDeleteMenuOnModuleAppSelection
        ]
    );
}