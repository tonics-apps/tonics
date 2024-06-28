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

function isValidURL(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false
    }
}

function createEventSource(url, retryTime = 2000, onMsg = null) {

    let eventSource = new EventSource(url);
    eventSource.addEventListener('message', (e) => {
        const data = JSON.parse(e.data);
        if (onMsg) {
            onMsg(data);
        }
    });

    eventSource.addEventListener('close', (e) => {
        eventSource.close(); // Close the current connection
        setTimeout(() => {
            createEventSource(url, retryTime, onMsg); // Retry connection after a custom delay
        }, retryTime); // Retry after 2 second
    });
}

const preLog = document.getElementById("terminal")?.querySelector('pre');
if (preLog.lastElementChild) {
    preLog.scrollTop = preLog.scrollHeight;
}
if (preLog.dataset.msg !== '') {
    createEventSource(preLog.dataset.msg, 2000, (msg) => {
        console.log(msg)
        if (msg.type === 'LOGGER') {
            let frag = '<hr>' + msg.data;
            preLog.insertAdjacentHTML('beforeend', frag);
            if (preLog.lastElementChild) {
                preLog.scrollTop = preLog.scrollHeight;
            }
        }
    })
}