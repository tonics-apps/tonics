
try {
    new MenuToggle('.form-and-filter', new Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Form-Filter")
}

let formFilter = document.querySelector('form');
if (formFilter) {
    formFilter.addEventListener('submit', (e) => {
        let target = e.target;
        let inputs = target.querySelectorAll('input');
        if (inputs.length > 0) {
            inputs.forEach((input) => {
                let value = input.value;
                value.trim();
                if (!value) {
                    input.disabled = true;
                }
            })
        }
    })
}