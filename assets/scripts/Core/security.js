import { validateEmailInput, validateTextInput, validatePasswordInput, checkFields, checkPasswords } from './validator';
import { xhrRequest } from './xhr';
import { displayMessage } from './flash';
import { redirectToHomepage, redirectTo } from './router';


export function registerLogin()
{
    const registerLoginForm = document.getElementById('register-login-form');

    if (registerLoginForm) {
        const emailInput = registerLoginForm.querySelector('input');
        const validationButton = registerLoginForm.querySelector('button');
        emailInput.addEventListener('blur', function () {
            validateEmailInput(this, validationButton);
        });

        registerLoginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (validateEmailInput(emailInput, validationButton)) {
                xhrRequest('/register-login/', 'POST', new FormData(this), function (result) {
                    const messageZone = registerLoginForm.querySelector('[data-message]');
                    if (result.status === 'SUCCESS') {
                        redirectTo(result.content);
                    } else {
                        displayMessage(messageZone, 'error', result.content);
                    }
                });
            }
        });
    }
}


export function register()
{
    const registerForm = document.getElementById('register-form');

    if (registerForm) {
        const inputs = registerForm.querySelectorAll('input');
        const validationButton = registerForm.querySelector('button');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (input.type === 'text') {
                    validateTextInput(this, validationButton);
                } else if (input.type === 'password') {
                    validatePasswordInput(this, validationButton);
                }
            });
        });

        const passwordInputs = registerForm.querySelectorAll('input[type="password"]');
        checkPasswordFields(passwordInputs, validationButton);

        registerForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkPasswords(passwordInputs[0], passwordInputs[1], validationButton) && checkFields(inputs, validationButton)) {
                xhrRequest('/register/', 'POST', new FormData(this), function (result) {
                    const messageZone = registerForm.querySelector('[data-message]');
                    if (result.status === 'SUCCESS') {
                        displayMessage(messageZone, 'success', result.content);
                        setTimeout(function () {
                            redirectTo('/login');
                        }, 2000);
                    } else {
                        displayMessage(messageZone, 'error', result.content);
                    }
                });
            }
        });
    }
}


export function confirmAccount()
{
    const confirmForm = document.getElementById('confirm-form');

    if (confirmForm) {
        const passwordInput = confirmForm.querySelector('input');
        const validationButton = confirmForm.querySelector('button');
        passwordInput.addEventListener('blur', function () {
            validatePasswordInput(this, validationButton);
        });

        confirmForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields([passwordInput], validationButton)) {
                xhrRequest('/register/confirm/', 'POST', new FormData(this), function (result) {
                    const messageZone = confirmForm.querySelector('[data-message]');
                    if (result.status === 'SUCCESS') {
                        displayMessage(messageZone, 'success', result.content);
                        setTimeout(function () {
                            redirectToHomepage();
                        }, 2000);
                    } else {
                        displayMessage(messageZone, 'error', result.content);
                    }
                });
            }
        });
    }
}


export function login()
{
    const loginForm = document.getElementById('login-form');

    if (loginForm) {
        const passwordInput = loginForm.querySelector('input');
        const validationButton = loginForm.querySelector('button');
        passwordInput.addEventListener('blur', function () {
            validatePasswordInput(this, validationButton);
        });

        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields([passwordInput], validationButton)) {
                xhrRequest('/login/', 'POST', new FormData(this), function (result) {
                    const messageZone = loginForm.querySelector('[data-message]');
                    if (result.status === 'SUCCESS') {
                        displayMessage(messageZone, 'success', result.content);
                        setTimeout(function () {
                            redirectToHomepage();
                        }, 2000);
                    } else {
                        displayMessage(messageZone, 'error', result.content);
                    }
                });
            }
        });
    }
}


export function reset()
{
    const resetLink = document.getElementById('reset-link');
    const loginForm = document.getElementById('login-form');

    if (resetLink && loginForm) {
        resetLink.addEventListener('click', function (event) {
            event.preventDefault();
            xhrRequest('/reset-password/', 'GET', null, function (result) {
                const messageZone = loginForm.querySelector('[data-message]');
                if (result.status === 'SUCCESS') {
                    displayMessage(messageZone, 'success', result.content);
                    setTimeout(function () {
                        redirectTo('/login');
                    }, 2000);
                } else {
                    displayMessage(messageZone, 'error', result.content);
                }
            });
        });
    }
}


export function changePassword()
{
    const changeForm = document.getElementById('change-form');

    if (changeForm) {
        const passwordInputs = changeForm.querySelectorAll('input[type="password"]');
        const validationButton = changeForm.querySelector('button');
        passwordInputs.forEach(function (passwordInput) {
            passwordInput.addEventListener('blur', function () {
                validatePasswordInput(this, validationButton);
            });
        })

        checkPasswordFields(passwordInputs, validationButton);

        changeForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkPasswords(passwordInputs[0], passwordInputs[1], validationButton) && checkFields(passwordInputs, validationButton)) {
                xhrRequest('/change-password/', 'POST', new FormData(this), function (result) {
                    const messageZone = changeForm.querySelector('[data-message]');
                    if (result.status === 'SUCCESS') {
                        displayMessage(messageZone, 'success', result.content);
                        setTimeout(function () {
                            redirectTo('/login');
                        }, 2000);
                    } else {
                        displayMessage(messageZone, 'error', result.content);
                    }
                });
            }
        });
    }
}


function checkPasswordFields(passwordInputs, validationButton)
{
    passwordInputs[0].addEventListener('input', function () {
        calcPasswordStrength(this.value);
    });
    passwordInputs[0].addEventListener('blur', function () {
        checkPasswords(passwordInputs[0], passwordInputs[1], validationButton);
    });
    passwordInputs[1].addEventListener('blur', function () {
        checkPasswords(passwordInputs[0], passwordInputs[1], validationButton);
    });
}


function calcPasswordStrength(password)
{
    let score = 0;
    if (/[A-Z]/.test(password)) {
        score++;
    }
    if (/[0-9]/.test(password)) {
        score++;
    }
    if (/[^[a-zA-Z0-9]/.test(password)) {
        score++;
    }
    if (password.length < 8) {
        score = 0;
    } else if (password.length <= 12) {
        score++;
    } else {
        score += 2;
    }

    const passwordStrength = document.querySelector('[data-password-strength]');
    const passwordStrengthItems = passwordStrength.querySelectorAll('div');

    if (passwordStrength && passwordStrengthItems) {
        passwordStrength.classList.remove('hidden');
        passwordStrengthItems.forEach(function (passwordStrengthItem) {
            passwordStrengthItem.classList.remove('password-low', 'password-medium', 'password-strong', 'password-excellent');
        });
        if (score <= 2) {
            passwordStrengthItems[0].classList.add('password-low');
        } else if (score === 3) {
            passwordStrengthItems[0].classList.add('password-low');
            passwordStrengthItems[1].classList.add('password-medium');
        } else if (score === 4) {
            passwordStrengthItems[0].classList.add('password-low');
            passwordStrengthItems[1].classList.add('password-medium');
            passwordStrengthItems[2].classList.add('password-strong');
        } else {
            passwordStrengthItems[0].classList.add('password-low');
            passwordStrengthItems[1].classList.add('password-medium');
            passwordStrengthItems[2].classList.add('password-strong');
            passwordStrengthItems[3].classList.add('password-excellent');
        }
    }
}