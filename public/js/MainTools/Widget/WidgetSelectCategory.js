// ************************ Auto-Generate Cat Slug For Category Create/Edit ***************** //
let categorySelector = document.querySelector('[data-widget-select-category="true"]');
let mainURLSlug = document.querySelector('input[name=cat_url_slug]');
if (categorySelector && mainURLSlug) {
    categorySelector.addEventListener('change', function (e) {
        let selected = categorySelector.options[categorySelector.selectedIndex];
        mainURLSlug.value = selected.getAttribute("data-path");
        e.preventDefault();
    });
}