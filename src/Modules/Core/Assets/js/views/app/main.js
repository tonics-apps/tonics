/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class DisableDeleteMenuOnModuleAppSelection {

    constructor(event) {
        let dataTable = event.dataTable;
        let dataTdOfTType;
        let trEl = event.elementTarget.closest('tr');
        if ((dataTdOfTType = trEl?.querySelector('[data-td="type"]'))) {
            if (dataTdOfTType.innerText.toLowerCase() === 'module') {
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
        let trEl = event.elementTarget.closest('tr');
        if (trEl?.querySelector('[data-td="update_available_Yes"]')) {
            dataTable.activateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
        } else {
            dataTable.deActivateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
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
            if (getAllSelectedTrElement.length > 0) {
                dataTable.collateTdFromTrAndPushToSaveTo(getAllSelectedTrElement, appUpdateData.appUpdateElements, appUpdateData.headers);
                appUpdateData.type.push(dataTable.apiEvents().APP_UPDATE_EVENT);

                window.TonicsScript.promptToast("Update Operation Might Be Irreversible", "Proceed To Update", () => {
                    updateEvent.querySelector('.loading-animation').classList.remove('d:none');
                    dataTable.deActivateMenus([dataTable.menuActions().APP_UPDATE_EVENT]);
                    dataTable.sendPostRequest(appUpdateData, (data) => {
                        updateEvent.querySelector('.loading-animation').classList.add('d:none');
                        if (data.status === 200) {
                            window.TonicsScript.successToast(data.message);
                        }
                    }, (err) => {
                        let errMsg = err?.message;
                        if (!errMsg) {
                            errMsg = 'An error occurred updating app';
                        }
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