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

let getAllTonicsFieldTabContainer = document.querySelectorAll('.tonicsFieldTabsContainer');
if (getAllTonicsFieldTabContainer) {
    getAllTonicsFieldTabContainer.forEach(eachTabContainer => {
        if (eachTabContainer.id) {
            let parentID = eachTabContainer.id;
            let pathName = window.location.pathname;
            // get all items from session and check it...
            let tonicsTabFieldIDLocalStorage = localStorage.getItem(`tonicsTabFieldID_${parentID}_${pathName}`);
            if (tonicsTabFieldIDLocalStorage) {
                let tabID = eachTabContainer.querySelector(`input[data-unique="${tonicsTabFieldIDLocalStorage}"]`);
                if (tabID?.tagName.toString() === 'INPUT' && tabID?.parentElement === eachTabContainer) {
                    tabID.checked = true;
                }
            }

            eachTabContainer.addEventListener('click', (e) => {
                let el = e.target;
                if (el?.tagName.toString() === 'INPUT' && el?.parentElement === eachTabContainer) {
                    let inputID = el.dataset?.unique;
                    // set local storage item
                    localStorage.setItem(`tonicsTabFieldID_${parentID}_${pathName}`, inputID);
                }
            });
        }
    });
}

