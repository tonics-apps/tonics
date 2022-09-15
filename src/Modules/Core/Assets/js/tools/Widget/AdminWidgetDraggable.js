
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