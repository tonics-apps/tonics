// You can access all script module using window.myModule
let menuArranger = document.querySelector('.menu-arranger');
window.onload = () => nativeFieldModules();
function nativeFieldModules() {
    if (menuArranger){
        menuArranger.addEventListener('change', (e) => {
            let el = e.target;
            if (el.closest('select[name=dateType]')){
                let dateSelect = el.closest('select[name=dateType]');
                let dateParent = dateSelect.closest("[data-widget-form='true']");
                let dateMin = dateParent.querySelector("[name='min']");
                let dateMax = dateParent.querySelector("[name='max']");
                if (dateMin){
                    dateMin.type = dateSelect.value;
                }
                if (dateMax){
                    dateMax.type = dateSelect.value;
                }
            }

            if (el.closest('.rowColumn')){
                let row = el.closest('.rowColumn').querySelector("[name='row']").value;
                let column = el.closest('.rowColumn').querySelector("[name='column']").value;
                updateRowCol(row, column, el.closest('.rowColumn').closest('.row-col-parent'))
            }
        })

        menuArranger.addEventListener('click', (e) => {
            let el = e.target;
            if (el.closest('.row-col-item')){
                // If there is a nested list item, we prevent further click from this handler,
                // this way things can be less confusing when we have multiple nested items
                if (el.closest('.row-col-item').querySelector('li')){
                    el.closest('.row-col-item').dataset.prevent_click = 'true'
                } else {
                    el.closest('.row-col-item').dataset.prevent_click = 'false'
                }
                if (el.closest('.row-col-item').dataset.prevent_click === 'false'){
                    // checkbox.checked = false;
                    let rowColItem = el.closest('.row-col-item').querySelector('input[name=cell]');
                    // Toggle Click
                    rowColItem.checked = !rowColItem.checked;
                }
            }
        });
    }
}

nativeFieldModules();
function updateRowCol(row, col, parent) {
    let times = row * col;
    let rowColumnItemContainer = parent.querySelector('.rowColumnItemContainer');
    let rowColItems = parent.querySelectorAll('.rowColumnItemContainer > .row-col-item');
    let cellItems = times - rowColItems.length;
    if (Math.sign(cellItems) === -1){
        // convert it to positive, and store in toRemove
        let toRemove = -cellItems;
        for(let i = 1; i <= toRemove; i++){
            rowColumnItemContainer.removeChild(rowColumnItemContainer.lastElementChild);
        }
    }

    if (Math.sign(cellItems) === 1){
        for(let i = 1; i <= cellItems; i++){
            rowColumnItemContainer.insertAdjacentHTML('beforeend', getCellForm());
        }
    }
    rowColumnItemContainer.style.setProperty('--row', row);
    rowColumnItemContainer.style.setProperty('--column', col);
}

function getCellForm() {
    let slugID = (Math.random()*1e32).toString(36);
    return `
<ul style="margin-left: 0;" class="row-col-item">
   <div class="form-group">
      <label class="menu-settings-handle-name" for="cell-select-${slugID}">Select & Choose Field
        <input id="cell-select-${slugID}" type="checkbox" name="cell">
      </label>
     </div>
</ul>
`
}

let inputTitle = document.querySelector('[name="post_title"]');
if (!inputTitle){
    inputTitle = document.querySelector('[name="track_title"]');
}

if (inputTitle){
    inputTitle.addEventListener('input', (e) => {
        let el = e.target, inputTitle = el.value;
        let seo_title = document.querySelector('[name="seo_title"]'),
            og_title = document.querySelector('[name="og_title"]');
        if (inputTitle){
            if (seo_title){
                seo_title.value = (seo_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(seo_title.getAttribute('maxlength')))
                    : inputTitle;
            }
            if (og_title){
                og_title.value = (og_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(og_title.getAttribute('maxlength')))
                    : inputTitle;
            }
        }
    });
}