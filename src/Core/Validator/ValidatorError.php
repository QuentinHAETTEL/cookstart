<?php

namespace App\Core\Validator;

class ValidatorError
{
    private string $key;
    private string $rule;
    private array $attributes;
    private array $messages = [
        'required' => 'Le champ %s est requis',
        'empty' => 'Le champ %s ne peut pas être vide',
        'betweenLength' => 'Le champ %s doit contenir entre %d et %d caractères',
        'minlength' => 'Le champ %s doit contenir au moins %d caractères',
        'maxlength' => 'Le champ %s doit contenir au maximum of %d caractères',
        'email' => 'Le champ %s doit être une adresse email valide',
        'boolean' => 'Le champ %s doit être un booléen',
        'datetime' => 'Le champ %s doit être une date valide (%d)',
    ];


    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }


    public function __toString(): string
    {
        $parameters = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);
        return (string)call_user_func_array('sprintf', $parameters);
    }
}
