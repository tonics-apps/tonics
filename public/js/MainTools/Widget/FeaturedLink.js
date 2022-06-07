// FOR FEATURED IMAGE
document.querySelector('main').addEventListener('click', featuredLinkHandler);
let  featuredLinkInput, featuredLinkWindowInstance = null;
function featuredLinkHandler(e){
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (parent) {
        featuredLinkInput = parent.querySelector('[data-widget-file-url="true"]');
    }

    if (el.classList.contains('tonics-featured-link')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            featuredLinkWindowInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd')) {
        if (featuredLinkInput) {
            if (featuredLinkInput) {
                featuredLinkInput.value = data.value;
            }
            featuredLinkWindowInstance.close();
        }
    }
});
