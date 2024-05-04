
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

// FOR FEATURED IMAGE
if (document.querySelector('main')){
    document.querySelector('main').addEventListener('click', featuredLinkHandler);
}

if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelectLinkHandler = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelectLinkHandler.length > 0){
        tinySelectLinkHandler[0].addEventListener('click', featuredLinkHandler);
    }
}

let  featuredLinkInput, featuredLinkWindowInstance = null;
function featuredLinkHandler(e){
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (parent) {
        featuredLinkInput = parent.querySelector('[data-widget-file-url="true"]');
    }

    if (el.classList.contains('tonics-featured-link')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            featuredLinkWindowInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd')) {
        if (featuredLinkInput) {
            if (featuredLinkInput) {
                featuredLinkInput.value = data.value.replace(siteURL, '');
            }
            featuredLinkWindowInstance.close();
        }
    }
});
