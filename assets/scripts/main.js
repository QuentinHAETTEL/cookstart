import '@fortawesome/fontawesome-free/js/all.js';

import { registerLogin, register, confirmAccount, login, reset, changePassword } from './Core/security';
import { visibleIndication, visiblePassword } from './Core/HTML/visible';

window.addEventListener('DOMContentLoaded', function () {

    registerLogin();
    register();
    confirmAccount();
    login();
    reset();
    changePassword();

    visibleIndication();
    visiblePassword();
});