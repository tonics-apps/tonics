
let draggable = document.getElementsByClassName('draggable'),
    parent = '.license-arranger',
    widgetChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    licenseSlug = document.querySelector('input[name="license_slug"]').value,
    licenseID = document.querySelector('input[name="license_id"]').value,
    widgetFormCollected = new Map();

new MenuToggle(parent, new Query())
    .settings(widgetChild, '.dropdown-toggle', '.license-widget-information')
    .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
    .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
    .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
    .closeOnClickOutSide(false)
    .stopPropagation(false)
    .run();

new Draggables(parent)
    .settings(widgetChild, ['.license-widget-information', 'legend'], false) // draggable element
    .onDragDrop(function (element, self) {
        let elementDropped = self.getDroppedTarget().closest(widgetChild);
        let elementDragged = self.getDragging().closest(widgetChild);
        if (elementDropped !== elementDragged && top || bottom){
            // swap element
            swapNodes(elementDragged, elementDropped, self.draggingOriginalRect);
            sensitivity = 0;
            top = false; bottom = false;
        }
    }).onDragTop((element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheTop = element.previousElementSibling;
        if (dragToTheTop && dragToTheTop.classList.contains('menu-arranger-li')){
            top = true;
        }
    }
}).onDragBottom( (element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheBottom = element.nextElementSibling;
        if (dragToTheBottom && dragToTheBottom.classList.contains('menu-arranger-li')) {
            bottom = true;
        }
    }
}).run();

let licenseArranger = document.getElementsByClassName('license-arranger')[0];
function generateNewLicenseForm() {
    return `<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <fieldset
            class="width:100% padding:default d:flex justify-content:center pointer-events:none">
            <legend class="bg:pure-black color:white padding:default pointer-events:none d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">New License</span>
                <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <form class="widgetSettings d:none flex-d:column license-widget-information pointer-events:all owl width:100%">
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-name">License Name
                        <input id="license-name" name="name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="Change License Name" placeholder="Overwrite the license name">
                    </label>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-price">Price
                        <input id="license-price" name="price" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="0" placeholder="Overwrite the license name">
                    </label>
                </div>
                <div class="form-group position:relative">
                    <label class="menu-settings-handle-name screen-reader-text" for="license-contract">Licence Contract</label>
                        <input type="url" class="form-control input-checkout bg:white-one color:black border-width:default border:black license-contract" id="license-contract" 
                        name="licence_contract" placeholder="Upload Licence Contract, Can Be Empty" value="">
                    <button aria-pressed="false" type="button" class="license-contract-button act-like-button text show-password bg:pure-black color:white cursor:pointer">Upload Contract</button>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="is_enabled">Enable License
                         <select name="is_enabled" class="default-selector" id="is_enabled">
                                    <option value="1" selected="">True</option> 
                                    <option value="0">False</option>
                          </select>
                    </label>
                </div>
                <div class="form-group">
                    <button name="delete" class="delete-license-button listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete License Item
                    </button>
                </div>
            </form>
        </fieldset>
    </li>`
}

// add new license
let addNewLicenseButton = document.querySelector('.add-new-license');
if (addNewLicenseButton){
    addNewLicenseButton.addEventListener('click', (e) => {
        e.preventDefault();
        if (licenseArranger.querySelector('ul')){
            licenseArranger.querySelector('ul').insertAdjacentHTML('beforeend', generateNewLicenseForm());
        }
    });
}

// delete menu or widget
if (licenseArranger){
    licenseArranger.addEventListener('click', (e) => {
        e.preventDefault();
        let el = e.target;
        if (el.classList.contains('delete-license-button')){
            let arranger = el.closest('.draggable');
            if (arranger){
                arranger.remove();
            }
        }
    });
}

function getListDataArray() {
    if(draggable){
        let widgetSettingsEl = document.querySelectorAll('.widgetSettings'), licenseArray = [],
            i = 0;
        widgetSettingsEl.forEach(form => {
            let widgetSettings = {};
            if (form.tagName === 'FORM'){
                let widgetFormData = new FormData(form);
                widgetFormData.forEach((value, key) => {
                    widgetSettings[key] = value;
                });

                licenseArray.push(widgetSettings);
            }
        });
        return licenseArray;
    }
}

// License Upload Contract
let windowInstanceForLicenceContract = null;
let licenceContractParent = null;
document.addEventListener('click', (e) => {
    let el = e.target;
    if (el.classList.contains('license-contract-button')){
        if (tonicsFileManagerURL) {
            licenceContractParent = el.parentElement;
            let windowFeatures = "left=95,top=100";
            windowInstanceForLicenceContract = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }
});

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd') && data.cmd === 'tonics:DocLink') {
        if (licenceContractParent){
            let licenceContractInput = licenceContractParent.querySelector('.license-contract');
            licenceContractInput.value = data.value;
            windowInstanceForLicenceContract.close();
        }
    }
});

// save license builder
let saveAllLicense = document.querySelector('.save-license-builder-changes'),
    saveLicenseChangesForm = document.getElementById('saveLicenseBuilderItems');
if(saveAllLicense && saveLicenseChangesForm){
    saveAllLicense.addEventListener('click', function (e) {
        e.preventDefault();
        addHiddenInputToForm(saveLicenseChangesForm, 'licenseSlug', licenseSlug);
        addHiddenInputToForm(saveLicenseChangesForm, 'licenseDetails', JSON.stringify(getListDataArray()));
        saveLicenseChangesForm.submit();
    })
}