<?php

namespace App\Core\Validator;

use DateTime;

class Validator
{
    private array $parameters;
    private array $errors = [];


    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }


    public function isRequired(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if ($value === null) {
                $this->addError($key, 'required');
            }
        }
        return $this;
    }


    public function isNotEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if ($value === null || empty($value)) {
                $this->addError($key, 'empty');
            }
        }
        return $this;
    }


    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);
        if ($min !== null && $max !== null && ($length < $min || $length > $max)) {
            $this->addError($key, 'betweenLength', [$min, $max]);
            return $this;
        }

        if ($min !== null && $length < $min) {
            $this->addError($key, 'minlength', [$min]);
            return $this;
        }

        if ($max !== null && $length > $max) {
            $this->addError($key, 'maxlength', [$max]);
            return $this;
        }

        return $this;
    }


    public function isEmail(string $key): self
    {
        $value = $this->getValue($key);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($key, 'email');
        }

        return $this;
    }


    public function isBool(string $key): self
    {
        $value = $this->getValue($key);
        if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->addError($key, 'boolean');
        }

        return $this;
    }


    public function isDateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        if ($date === false || $errors['error_count'] > 0 || $errors['warning_count'] > 0) {
            $this->addError($key, 'datetime', [$format]);
        }

        return $this;
    }


    /**
     * @return mixed|null
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        return null;
    }


    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidatorError($key, $rule, $attributes);
    }


    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = $error->__toString();
        }
        return $errors;
    }


    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
