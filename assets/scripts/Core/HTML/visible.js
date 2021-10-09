export function visibleIndication()
{
    const visibleToggles = document.querySelectorAll('[data-visible]');

    visibleToggles.forEach(function (visibleToggle) {
        visibleToggle.addEventListener('click', function () {
            const visibleElement = document.getElementById(visibleToggle.getAttribute('data-visible'));
            visibleElement.classList.remove('hidden');
        });
    });
}


export function visiblePassword()
{
    const showPasswordToggles = document.querySelectorAll('[data-password]');

    showPasswordToggles.forEach(function (showPasswordToggle) {
        showPasswordToggle.addEventListener('click', function () {
            let input = document.getElementById(this.getAttribute('data-password'));
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('form__input--show');
                this.classList.add('form__input--hide');
            } else {
                input.type = 'password';
                this.classList.remove('form__input--hide');
                this.classList.add('form__input--show');
            }
        });
    });
}