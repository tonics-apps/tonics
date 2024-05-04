
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