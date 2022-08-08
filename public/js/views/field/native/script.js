// You can access all script module using window.myModule
let menuArranger = document.querySelector('.menu-arranger');
window.onload = () => nativeFieldModules();

function nativeFieldModules() {
    if (menuArranger) {
        menuArranger.addEventListener('change', (e) => {
            let el = e.target;
            if (el.closest('select[name=dateType]')) {
                let dateSelect = el.closest('select[name=dateType]');
                let dateParent = dateSelect.closest("[data-widget-form='true']");
                let dateMin = dateParent.querySelector("[name='min']");
                let dateMax = dateParent.querySelector("[name='max']");
                if (dateMin) {
                    dateMin.type = dateSelect.value;
                }
                if (dateMax) {
                    dateMax.type = dateSelect.value;
                }
            }

            if (el.closest('.rowColumn')) {
                let row = el.closest('.rowColumn').querySelector("[name='row']").value;
                let column = el.closest('.rowColumn').querySelector("[name='column']").value;
                updateRowCol(row, column, el.closest('.rowColumn').closest('.row-col-parent'))
            }
        })

        menuArranger.addEventListener('click', (e) => {
            let el = e.target;
            if (el.closest('.row-col-item')) {
                // If there is a nested list item, we prevent further click from this handler,
                // this way things can be less confusing when we have multiple nested items
                if (el.closest('.row-col-item').querySelector('li')) {
                    el.closest('.row-col-item').dataset.prevent_click = 'true'
                } else {
                    el.closest('.row-col-item').dataset.prevent_click = 'false'
                }
                if (el.closest('.row-col-item').dataset.prevent_click === 'false') {
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
    if (Math.sign(cellItems) === -1) {
        // convert it to positive, and store in toRemove
        let toRemove = -cellItems;
        for (let i = 1; i <= toRemove; i++) {
            rowColumnItemContainer.removeChild(rowColumnItemContainer.lastElementChild);
        }
    }

    if (Math.sign(cellItems) === 1) {
        for (let i = 1; i <= cellItems; i++) {
            rowColumnItemContainer.insertAdjacentHTML('beforeend', getCellForm());
        }
    }
    rowColumnItemContainer.style.setProperty('--row', row);
    rowColumnItemContainer.style.setProperty('--column', col);
}

function getCellForm() {
    let slugID = (Math.random() * 1e32).toString(36);
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
if (!inputTitle) {
    inputTitle = document.querySelector('[name="track_title"]');
}

if (inputTitle) {
    inputTitle.addEventListener('input', (e) => {
        let el = e.target, inputTitle = el.value;
        let seo_title = document.querySelector('[name="seo_title"]'),
            og_title = document.querySelector('[name="og_title"]');
        if (inputTitle) {
            if (seo_title) {
                seo_title.value = (seo_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(seo_title.getAttribute('maxlength')))
                    : inputTitle;
            }
            if (og_title) {
                og_title.value = (og_title.hasAttribute('maxlength'))
                    ? inputTitle.slice(0, parseInt(og_title.getAttribute('maxlength')))
                    : inputTitle;
            }
        }
    });
}

// TABLE OF CONTENT:
let tocHeader = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
if (tocHeader.length > 0) {
    let currentLevel = 0, tree = [], lastAddedElementToTree = 0, breakLoopBackward = false, result = '<ul>', item = {}, childStack = [];
    tocHeader.forEach((header, index) => {
        if (header.textContent.length > 0) {
            let headerIDSlug = slug(header.textContent);
            let headerText = header.textContent;
            // let headerIDSlug = myModule.slug(header.textContent);
            if (header.hasAttribute('id') === false) {
                header.id = headerIDSlug;
            } else if (header.id.trim() === '') {
                header.id = headerIDSlug;
            }
            currentLevel = parseInt(header.tagName[1]);

            item = {
                'level': currentLevel,
                'headerID': headerIDSlug,
                'headerText': headerText,
            };
            item.data = `<li data-heading_level="${currentLevel}"><a href="#${headerIDSlug}"><span class="toctext">${headerText}</span></a>`;
            if (tree.length === 0){
                pushToTree(item)
            } else {
                // if currentLevel is same as previous, close previous and add unto it (for efficiency)
                if (tree[lastAddedElementToTree].level === currentLevel){
                    tree[lastAddedElementToTree].data += '</li>' + item.data;
                }

                if (currentLevel > tree[lastAddedElementToTree].level){
                    tree[lastAddedElementToTree].data += '<ul>'
                    pushToTree(item);
                    lastAddedElementToTree = tree.length - 1;
                    childStack.push(item)
                }

                if (currentLevel < tree[lastAddedElementToTree].level){
                    // loop backward..
                    handleCurrentIsLesserThanPrevious();
                }

                // last element
                if (index === tocHeader.length - 1){
                    tree[lastAddedElementToTree].data += '</li>'
                }
            }
        }
    });

    function handleCurrentIsLesserThanPrevious() {
        let timesToClose = 0, encounteredSameLevel = false;
        for (const treeData of loopTreeBackward(childStack)) {
            if (treeData.level > currentLevel){
                treeData.data += '</li>'
                childStack.pop();
                timesToClose++;
            }
            if (treeData.level === currentLevel){
                encounteredSameLevel = true;
                breakLoopBackward = true;
            }
        }
        item.data = '</ul></li>'.repeat(timesToClose) + item.data;
        pushToTree(item);
        lastAddedElementToTree = tree.length - 1;
    }

    function pushToTree(item) {
        item.index = tree.length;
        tree.push(item);
    }

    function *loopTreeBackward(treeToLoop = null) {
        if (treeToLoop === null){
            treeToLoop = tree;
        }
        for (let i = treeToLoop.length - 1; i >= 0; i--){
            if (breakLoopBackward){break;}
            yield treeToLoop[i];
        }
        breakLoopBackward = false;
    }

    tree.forEach((item) => {
        result += item.data;
    });
    result += '</ul>';
    //document.querySelector('body').insertAdjacentHTML('afterbegin', result)
    console.log(tocHeader, tree, result);
}