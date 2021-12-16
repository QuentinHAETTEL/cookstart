import {validateTextInput, validateFileInput, validateSelect, checkPasswords, checkFields} from '../Core/validator';
import {xhrRequest} from '../Core/xhr';
import {redirectTo} from '../Core/router';
import {displayMessage} from '../Core/flash';

export function addIngredient()
{
    const addIngredientForm = document.getElementById('add-ingredient-form');

    if (addIngredientForm) {
        const inputs = addIngredientForm.querySelectorAll('input, select');
        const validationButton = addIngredientForm.querySelector('button');
        inputs.forEach(function (input) {
            input.addEventListener('blur', function () {
                if (input.type === 'text') {
                    validateTextInput(this, validationButton);
                } else if (input.type === 'file') {
                    validateFileInput(this, validationButton, 'image');
                } else if (input.tagName === 'SELECT') {
                    validateSelect(this, validationButton);
                }
            });
        });

        addIngredientForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (checkFields(inputs, validationButton)) {
                xhrRequest('/ingredients/add/', 'POST', new FormData(this), function (result) {
                    const messageZone = addIngredientForm.querySelector('[data-message]');
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