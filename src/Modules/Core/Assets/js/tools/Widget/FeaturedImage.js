
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
let featuredMain = document.querySelector('main');
if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelect = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelect.length > 0){
        tinySelect[0].addEventListener('click', featuredImageHandler);
    }
}
if (featuredMain){
    featuredMain.addEventListener('click', featuredImageHandler);
}
let featuredImageWithSRC, featuredImageInput, featuredImageInputName, removeFeaturedImage, windowInstance = null;

function featuredImageHandler(e) {
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (!parent) {
        parent =  el.closest('form');
    }
    if (parent) {
        featuredImageWithSRC = parent.querySelector('[class^="image:featured-image"]');
        featuredImageInput = parent.querySelector('.tonics-featured-image');
        featuredImageInputName = parent.querySelector('[data-widget-image-name="true"]');
        removeFeaturedImage = parent.querySelector('.remove-featured-image');
    }

    if (el.classList.contains('tonics-featured-image')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            windowInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    } else if (el.classList.contains('remove-featured-image')) {
        if (featuredImageInputName) {
            featuredImageInputName.value = '';
        }
        featuredImageWithSRC.src = '';
        featuredImageInput.classList.remove('d:none');
        removeFeaturedImage.classList.add('d:none');
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd') && data.cmd === 'tonics:ImageLink') {
        if (featuredImageWithSRC && featuredImageInput) {
            let imageLink = data.value.replace(siteURL, '');
            if (featuredImageInputName) {
                featuredImageInputName.value = imageLink;
            }
            featuredImageWithSRC.src = imageLink;
            featuredImageInput.classList.add('d:none');
            removeFeaturedImage.classList.remove('d:none');
            windowInstance.close();
        }
    }
});

let ImageFeaturedImage = document.querySelectorAll('[class^="image:featured-image"]');
if (ImageFeaturedImage.length > 0) {
    ImageFeaturedImage.forEach((value, key) => {
        let parent = value.closest('[data-widget-form="true"]');
        if (!parent) {
            parent =  value.closest('form');
        }
        let featuredImageInput = parent.querySelector('.tonics-featured-image'),
            removeFeaturedImage = parent.querySelector('.remove-featured-image');

        let image = new Image();
        image.src = value.src;
        image.onload = function () {
            // image can be loaded
            if (featuredImageInput){
                featuredImageInput.classList.add('d:none');
            }
            if (removeFeaturedImage){
                removeFeaturedImage.classList.remove('d:none');
            }
        }
        image.onerror = () => {
            // image can't be loaded
        }
    })
}
