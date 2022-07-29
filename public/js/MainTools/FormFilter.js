try {
    new myModule.MenuToggle('.form-and-filter', new myModule.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Form-Filter")
}

let form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', (e) => {
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