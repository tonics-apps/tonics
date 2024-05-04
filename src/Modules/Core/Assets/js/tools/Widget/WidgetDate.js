
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

let widgetDateInput = document.querySelector('[data-widget-date="true"]'),
    widgetDateInputForPost = document.querySelector('[data-widget-date-forpost="true"]'),
    isWidgetDateInputChangedByHuman = false;

let widgetDateInputForPostInitVal = 'Publish';
if (widgetDateInputForPost) {
    widgetDateInputForPostInitVal = widgetDateInputForPost.innerText;
}
if (widgetDateInput){
    widgetDateInput.addEventListener('change', (e) => {
        let d_now = new Date();
        let d_inp = new Date(e.target.value);
        if (widgetDateInputForPost) {
            if (d_now.getTime() <= d_inp.getTime()) {
                // The date user selected is in the future
                widgetDateInputForPost.innerText = 'Schedule'
                return false;
            }
            // The date user selected is in the past
            widgetDateInputForPost.innerText = widgetDateInputForPostInitVal
        }
    });
}

if (widgetDateInput) {
    let isWidgetDataInputClicked = false, isWidgetDataInputChanged = false;
    widgetDateInput.addEventListener('click', () => {
        isWidgetDataInputClicked = true;
    });

    widgetDateInput.addEventListener('input', (e) => {
        isWidgetDataInputChanged = true;
        if (isWidgetDataInputClicked) {
            isWidgetDateInputChangedByHuman = true;
        }
    });

    // would fire when observerWidgetInputInView 10% area is visible
    const observerWidgetInputInView = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting === true) {
            // if human hasn't changed the date initially, we update it
            if (isWidgetDateInputChangedByHuman === false) {
                widgetDateInput.value = new Date().toLocaleString('en-CA', {
                    timeZone: siteTimeZone,
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    minute: 'numeric',
                    second: 'numeric',
                    hour: 'numeric',
                    hour12: false,
                }).replace(/, /gi, 'T');
            }
        }
    }, {threshold: [0.10]});
    observerWidgetInputInView.observe(widgetDateInput);
}