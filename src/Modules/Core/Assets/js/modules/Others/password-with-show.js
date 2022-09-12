let showPassContainer = document.querySelectorAll('.password-with-show');
if (showPassContainer){
    for (let i = 0, len = showPassContainer.length; i < len; i++) {
        showPassContainer[i].addEventListener('click', function (e) {
            let el = e.target;
            if (el.classList.contains('show-password')){
                let inputPass = showPassContainer[i].querySelector('input');
                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'false'){
                    el.setAttribute('aria-pressed', true);
                    el.innerText = 'Hide';
                    inputPass.type = 'text'
                    return;
                }

                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'true'){
                    el.setAttribute('aria-pressed', false);
                    inputPass.type = 'password';
                    el.innerText = 'Show';
                }
            }
        });
    }
}