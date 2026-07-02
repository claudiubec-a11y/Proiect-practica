<?php

/**
 * ============================================================================
 * core/Validator.php
 * ----------------------------------------------------------------------------
 * Validator minimal, fara dependente externe, folosit de toate controller-ele
 * pentru a valida datele primite din request inainte de a le trimite
 * modelelor.
 *
 * Utilizare:
 *   $validator = new Validator($data);
 *   $validator->required('email')->email('email');
 *   if ($validator->fails()) { Response::validationError($validator->errors()); }
 * ============================================================================
 */

declare(strict_types=1);

final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, ?string $label = null): self
    {
        $label = $label ?? $field;
        if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
            $this->errors[$field][] = "Campul {$label} este obligatoriu.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Adresa de email nu este valida.';
        }
        return $this;
    }

    public function minLength(string $field, int $length): self
    {
        if (!empty($this->data[$field]) && mb_strlen((string) $this->data[$field]) < $length) {
            $this->errors[$field][] = "Campul trebuie sa aiba minim {$length} caractere.";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = 'Valoarea trebuie sa fie numerica.';
        }
        return $this;
    }

    public function in(string $field, array $allowedValues): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !in_array($this->data[$field], $allowedValues, true)) {
            $this->errors[$field][] = 'Valoare invalida pentru acest camp.';
        }
        return $this;
    }

    public function date(string $field): self
    {
        if (!empty($this->data[$field])) {
            $d = DateTime::createFromFormat('Y-m-d', (string) $this->data[$field]);
            if (!$d || $d->format('Y-m-d') !== $this->data[$field]) {
                $this->errors[$field][] = 'Data trebuie sa fie in formatul YYYY-MM-DD.';
            }
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
