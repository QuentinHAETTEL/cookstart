export function validateTextInput(input, submitButton)
{
    let valid = input.value.length > 0 && input.value.length <= 255;
    if (valid) {
        displayValidation('success', input, submitButton);
    } else {
        displayValidation('error', input, submitButton);
    }

    return valid;
}


export function validateEmailInput(input, submitButton)
{
    const regex = /^([a-z0-9_\+\-]+)(\.[a-z0-9_\+\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,4}$/;

    let valid = regex.test(input.value.toLowerCase()) && input.value.length > 0 && input.value.length <= 255;
    if (valid) {
        displayValidation('success', input, submitButton);
    } else {
        displayValidation('error', input, submitButton);
    }

    return valid;
}


export function validatePasswordInput(input, submitButton)
{
    let valid = input.value.length > 8 && input.value.length <= 255 && /[a-z]+/.test(input.value) && /[A-Z]+/.test(input.value) && /[0-9]+/.test(input.value);
    if (valid) {
        displayValidation('success', input, submitButton);
    } else {
        displayValidation('error', input, submitButton);
    }

    return valid;
}


export function validateFileInput(input, submitButton, fileType = 'image')
{
    const allowedImageFormats = ['png', 'jpg', 'jpeg'];
    const allowedDocumentFormats = ['pdf'];
    let fileName = input.value;
    let fileExtension = fileName.substr(fileName.lastIndexOf('.') + 1, fileName.length).toLowerCase();

    let validExtension;
    if (fileType === 'image') {
        validExtension = allowedImageFormats.includes(fileExtension);
    } else {
        validExtension = allowedDocumentFormats.includes(fileExtension);
    }

    if (validExtension) {
        displayValidation('success', input, submitButton);
    } else {
        displayValidation('error', input, submitButton);
    }
}


export function validateSelect(input, submitButton)
{
    if (input.value) {
        displayValidation('success', input, submitButton);
    } else {
        displayValidation('error', input, submitButton);
    }
}


export function checkFields(inputs, validationButton)
{
    let isValid = true;
    inputs.forEach(function (input) {
        if (input.type === 'text') {
            isValid = isValid && validateTextInput(input, validationButton);
        } else if (input.type === 'password') {
            isValid = isValid && validatePasswordInput(input, validationButton);
        }
    });

    return isValid;
}


export function checkPasswords(password, confirmation, submitButton)
{
    let valid = password.value === confirmation.value;
    if (valid) {
        displayValidation('success', confirmation, submitButton);
    } else {
        displayValidation('error', confirmation, submitButton);
    }

    return valid;
}


function displayValidation(type = 'success', input, submitButton = null)
{
    if (type === 'success') {
        input.classList.remove('form__input--error');
        input.classList.add('form__input--success');
        submitButton.removeAttribute('disabled');
    } else if (type === 'error') {
        input.classList.remove('form__input--success');
        input.classList.add('form__input--error');
        submitButton.setAttribute('disabled', 'disabled');
    }
}



