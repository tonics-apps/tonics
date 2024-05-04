
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

// audio featured selection
if (document.querySelector('main')){
    document.querySelector('main').addEventListener('click', audioFeaturedHandler);
}

if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelectAudioHandler = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelectAudioHandler.length > 0){
        tinySelectAudioHandler[0].addEventListener('click', audioFeaturedHandler);
    }
}
let audioDemoInput, audioDemoInputName, removeAudioDemo, windowAudioFeaturedInstance = null;
function audioFeaturedHandler(e) {
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (!parent) {
        parent =  el.closest('form');
    }
    if (parent) {
        audioDemoInput = parent.querySelector('.tonics-audio-featured');
        audioDemoInputName = parent.querySelector('[data-widget-audio-url="true"]');
        removeAudioDemo = parent.querySelector('.remove-audio-demo');
    }

    if (el.classList.contains('tonics-audio-featured')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            windowAudioFeaturedInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }else if (el.classList.contains('remove-audio-demo')) {
        if (audioDemoInputName) {
            audioDemoInputName.value = '';
        }
        audioDemoInput.classList.remove('d:none');
        removeAudioDemo.classList.add('d:none');
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd') && data.cmd === 'tonics:MediaLink') {
        if (audioDemoInput) {
            if (audioDemoInputName) {
                audioDemoInputName.value = data.value.replace(siteURL, '');
            }
            audioDemoInput.classList.add('d:none');
            removeAudioDemo.classList.remove('d:none');
            windowAudioFeaturedInstance.close();
        }
    }
});
