
// For SLUG TITLE AND DATE
let inputTitle = document.querySelector('[data-widget-title-slugtochange="true"]'),
    widgetSlugToUpdate = document.querySelector('[data-widget-title-slugtoupdate="true"]');

if (inputTitle){
    inputTitle.addEventListener('input', (e) => {
        let el = e.target, slugTitle = el.value;
        if (slugTitle && widgetSlugToUpdate){
            slugTitle = slug(slugTitle);
            widgetSlugToUpdate.value = slugTitle;
        }
    });
}