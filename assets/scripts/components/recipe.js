import {validateTextInput, validateFileInput, validateNumberInput, validateTimeInput, validateSelect, validateTextarea, checkFields} from '../Core/validator';
import {xhrRequest} from '../Core/xhr';
import {redirectTo} from '../Core/router';
import {displayMessage} from '../Core/flash';

export function addRecipe()
{
    const addRecipeForm = document.getElementById('add-recipe-form');

    if (addRecipeForm) {
        const inputs = addRecipeForm.querySelectorAll('input, select, textarea');
        const validationButton = addRecipeForm.querySelector('button');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (input.type === 'text') {
                    validateTextInput(this, validationButton);
                } else if (input.type === 'file') {
                    validateFileInput(this, validationButton, 'image');
                } else if (input.type === 'number') {
                    validateNumberInput(this, validationButton);
                } else if (input.type === 'time') {
                    validateTimeInput(this, validationButton);
                } else if (input.tagName === 'SELECT') {
                    validateSelect(this, validationButton);
                } else if (input.tagName === 'TEXTAREA') {
                    validateTextarea(this, validationButton);
                }
            });
        });

        addRecipeForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields(inputs, validationButton)) {
                xhrRequest('/recipes/add/', 'POST', new FormData(this), function (result) {
                    const messageZone = addRecipeForm.querySelector('[data-message]');
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


export function addRecipeIngredient()
{
    const selectIngredient = document.getElementById('recipe-ingredient');

    if (selectIngredient) {
        selectIngredient.addEventListener('change', function () {
            xhrRequest('/ingredient/'+selectIngredient.value+'/get-unit', 'GET', null, function (result) {
                const unitLabel = document.getElementById('recipe-unit-label');
                unitLabel.innerHTML = result.content;
            });
        });
    }

    const addRecipeIngredientForm = document.getElementById('add-recipe-ingredient-form');

    if (addRecipeIngredientForm) {
        const inputs = addRecipeIngredientForm.querySelectorAll('input, select');
        const validationButton = addRecipeIngredientForm.querySelector('button');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (input.type === 'number') {
                    validateNumberInput(this, validationButton);
                } else if (input.tagName === 'SELECT') {
                    validateSelect(this, validationButton);
                }
            });
        });

        addRecipeIngredientForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields(inputs, validationButton)) {
                const recipe = addRecipeIngredientForm.querySelector('input[type="hidden"]').value;
                xhrRequest('/recipes/'+recipe+'/add-ingredient/', 'POST', new FormData(this), function (result) {
                    const messageZone = addRecipeIngredientForm.querySelector('[data-message]');
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


export function addRecipeInstruction()
{
    const input = document.getElementById('recipe-instruction');
    const addRecipeInstructionForm = document.getElementById('add-recipe-instruction-form');

    if (addRecipeInstructionForm) {
        const input = addRecipeInstructionForm.querySelector('input');
        const validationButton = addRecipeInstructionForm.querySelector('button');
        input.addEventListener('blur', function () {
            validateTextInput(this, validationButton);
        });

        addRecipeInstructionForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields(input, validationButton)) {
                const recipe = addRecipeInstructionForm.querySelector('input[type="hidden"]').value;
                xhrRequest('/recipes/'+recipe+'/add-ingredient/', 'POST', new FormData(this), function (result) {
                    const messageZone = addRecipeInstructionForm.querySelector('[data-message]');
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