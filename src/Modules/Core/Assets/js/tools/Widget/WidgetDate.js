
let widgetDateInput = document.querySelector('[data-widget-date="true"]'),
    widgetDateInputForPost = document.querySelector('[data-widget-date-forpost="true"]'),
    isWidgetDateInputChangedByHuman = false;

let widgetDateInputForPostInitVal = 'Publish';
if (widgetDateInputForPost) {
    widgetDateInputForPostInitVal = widgetDateInputForPost.innerText;
}
if (widgetDateInput){
    widgetDateInput.addEventListener('change', (e) => {
        let d_now = new Date();
        let d_inp = new Date(e.target.value);
        if (widgetDateInputForPost) {
            if (d_now.getTime() <= d_inp.getTime()) {
                // The date user selected is in the future
                widgetDateInputForPost.innerText = 'Schedule'
                return false;
            }
            // The date user selected is in the past
            widgetDateInputForPost.innerText = widgetDateInputForPostInitVal
        }
    });
}

if (widgetDateInput) {
    let isWidgetDataInputClicked = false, isWidgetDataInputChanged = false;
    widgetDateInput.addEventListener('click', () => {
        isWidgetDataInputClicked = true;
    });

    widgetDateInput.addEventListener('input', (e) => {
        isWidgetDataInputChanged = true;
        if (isWidgetDataInputClicked) {
            isWidgetDateInputChangedByHuman = true;
        }
    });

    // would fire when observerWidgetInputInView 10% area is visible
    const observerWidgetInputInView = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting === true) {
            // if human hasn't changed the date initially, we update it
            if (isWidgetDateInputChangedByHuman === false) {
                widgetDateInput.value = new Date().toLocaleString('en-CA', {
                    timeZone: siteTimeZone,
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    minute: 'numeric',
                    second: 'numeric',
                    hour: 'numeric',
                    hour12: false,
                }).replace(/, /gi, 'T');
            }
        }
    }, {threshold: [0.10]});
    observerWidgetInputInView.observe(widgetDateInput);
}