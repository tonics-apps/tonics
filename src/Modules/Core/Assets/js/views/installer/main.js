
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

let preElement = document.querySelector('.installation-pre'),
    form = document.querySelector('form');


function submitForm(event) {
    if (document.forms.hasOwnProperty('InstallerForm')){
        event.preventDefault();
        let formEl = document.forms.InstallerForm;
        let formData = new FormData(formEl);
        let formEntries = {};
        // Arrange The Form Data
        formData.forEach(function (value, key, parent) {
            formEntries[key] = value;
        })
        let preInstallerAPI = `/api/pre-installer`
        let XHR = new XHRApi();
        XHR.Post(preInstallerAPI, JSON.stringify(formEntries), function (err, data) {
            if (err) {
                err = JSON.parse(err);
                console.log(err);
            }
            if (data) {
                data = JSON.parse(data);
                if (data.hasOwnProperty('status')) {
                    if (data.status === 200) {
                        let eventSource = new EventSource('/api/installer');

                        // would have used error instead issue but there is already a built-in type named error
                        eventSource.addEventListener('issue', function(e) {
                            try{
                                const data = JSON.parse(e.data);
                                preCodeMessage(`<code class='color:red'>${data}</code>`);
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`)
                            }
                        }, false);

                        eventSource.addEventListener('message', function(e) {
                            try{
                                const data = JSON.parse(e.data);
                                console.log(data);
                                preCodeMessage(`<code>${data}</code>`);
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`)
                            }
                        }, false);

                        eventSource.addEventListener('close', function(e) {
                            preCodeMessage(`<code>Closed</code>`);
                            eventSource.close();
                            eventSource = null;
                        }, false);

                        eventSource.addEventListener('open', function(e) {
                            // Connection was opened.
                            console.log("Opening new connection");
                        }, false);

                        eventSource.addEventListener('redirect', function(e) {
                            try{
                                let url = JSON.parse(JSON.parse(e.data));
                                window.location.href = window.location.origin + url.page;
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`);
                            }
                        }, false);
                    }
                }
            }
        });
    }
}

function preCodeMessage(message = '') {
    if (preElement){
        preElement.insertAdjacentHTML('beforeend', message)
        if (preElement.lastElementChild){
            preElement.scrollTop = preElement.scrollHeight;
        }
    }
}

form.addEventListener('submit', submitForm);