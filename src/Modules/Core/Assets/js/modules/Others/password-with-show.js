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

let showPassContainer = document.querySelectorAll('.password-with-show');
if (showPassContainer){
    for (let i = 0, len = showPassContainer.length; i < len; i++) {
        showPassContainer[i].addEventListener('click', function (e) {
            let el = e.target;
            if (el.classList.contains('show-password')){
                let inputPass = showPassContainer[i].querySelector('input');
                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'false'){
                    el.setAttribute('aria-pressed', true);
                    el.innerText = 'Hide';
                    inputPass.type = 'text'
                    return;
                }

                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'true'){
                    el.setAttribute('aria-pressed', false);
                    inputPass.type = 'password';
                    el.innerText = 'Show';
                }
            }
        });
    }
}