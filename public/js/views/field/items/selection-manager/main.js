import * as myModule from "./script-combined.js";


let chooseMenuFields = document.querySelector('.choose-field-button');

chooseMenuFields.addEventListener('click', (e) => {
   let selectedFields = document.querySelectorAll('[data-selected="true"]'),
       selectedFieldSlug = [];
   selectedFields.forEach((field) => {
       selectedFieldSlug.push(field.dataset.field_id);
   });
    if (selectedFieldSlug.length > 0){
        let slug = {
            action: 'getFieldItems',
            fieldSlug: JSON.stringify(selectedFieldSlug)
        }
        let url = window.location.href + "?action=getFieldItems";
        new XHRApi({...{}, ...slug}).Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                console.log(data);
            }
        });
    }
    console.log(selectedFieldSlug);
});


