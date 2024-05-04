
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

let adminWidgetItemEl = document.querySelector('[class ^=admin-widget-item]');
if (adminWidgetItemEl){
    let adminWidgetItemClassList = adminWidgetItemEl.classList;
    let adminWidgetName = '';
    adminWidgetItemClassList.forEach(((value, key) => {
        if (value.startsWith('admin-widget-item')){
            adminWidgetName = '.' + value;
            return true;
        }
    }));

    try {
        new Draggables('.admin-widget')
            .settings(adminWidgetName, ['.admin-widget-information'], false) // draggable element
            .onDragDrop(function (element, self) {
                let elementDropped = element.closest(adminWidgetName);
                let elementDragged = self.getDragging().closest(adminWidgetName);
                if (elementDropped !== elementDragged){
                    // swap element
                    swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
                }
                //  console.log(elementDropped.getBoundingClientRect(), 'is dropped');
                // console.log(self.draggingOriginalRect, 'original dragged rect');
                // console.log(elementDragged.getBoundingClientRect(), 'was dragged');
            }).onDragRight(function (element) {
            // console.log(element, 'is drag to the right')
        }).onDragLeft(function (element) {
            //console.log(element, 'is drag to the left')
        }).onDragTop(function (element) {
            //console.log(element, 'is drag to the top')
        }).onDragBottom(function (element) {
            //console.log(element, 'is drag to the bottom')
        }).run();
    } catch (e) {
        console.error("An Error Occur Setting Up Draggable: admin-widget")
    }

}