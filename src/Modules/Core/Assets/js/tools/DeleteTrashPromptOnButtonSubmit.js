
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

const trashButtons = document.querySelectorAll('[data-click-onconfirmtrash="true"]');
const deleteButtons = document.querySelectorAll('[data-click-onconfirmdelete="true"]');

if (trashButtons) {
    let trashIsBusy = false;
    trashButtons.forEach((value, key) => {
        value.addEventListener('click', (e) => {
            let button = e.target;
            if (trashIsBusy === false) {
                promptToast("Do you want to Move Item To Trash?", "Move To Trash", () => {
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
                promptToast("Do you want to Delete Item?", "Delete Item", () => {
                    trashIsBusy = true;
                    button.type = 'submit'
                    button.click();
                    trashIsBusy = false;
                })
            }
        })
    });
}