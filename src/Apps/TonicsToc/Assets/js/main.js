
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

class OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

class OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

if (window?.parent.TonicsEvent?.EventConfig){
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldPreviewEvent.push(OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc);
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldSubmitEvent.push(OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc);
}

function handleTonicsToc(event, target) {
    if (target.closest('.tabs')) {
        const TocEditor = window.TonicsScript.TableOfContent('.entry-content');
        TocEditor.tocDepth(6).run();
        const headersFound = TocEditor.$tableOfContentDetails.noOfHeadersFound;
        const Tree = TocEditor.tocTree
        let fields = JSON.parse(event._postData);
        for (const i in fields) {
            let field = fields[i];
            if (field.hasOwnProperty('main_field_slug') && field.main_field_slug === 'app-tonicstoc'){
                if (!field.field_options?.tableOfContentData){
                    field.field_options = JSON.parse(field.field_options);
                }
                field.field_options.tableOfContentData = {
                    'headersFound': headersFound,
                    'tree': Tree
                };
            }
        }
        event._postData = JSON.stringify(fields);
    }
}