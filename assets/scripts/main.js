import '@fortawesome/fontawesome-free/js/all.js';

import { registerLogin, register, confirmAccount, login, reset, changePassword } from './Core/security';
import { visibleIndication, visiblePassword } from './Core/HTML/visible';
import { toggleMenu } from './Core/HTML/menu';
import { addIngredient } from './components/ingredients';
import { addRecipe, addRecipeIngredient } from './components/recipe';

window.addEventListener('DOMContentLoaded', function () {

    registerLogin();
    register();
    confirmAccount();
    login();
    reset();
    changePassword();

    visibleIndication();
    visiblePassword();

    toggleMenu();

    addIngredient();
    addRecipe();
    addRecipeIngredient();
});