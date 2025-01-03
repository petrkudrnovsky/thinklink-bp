<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueFilename extends Constraint
{
    public string $message = 'Soubor "{{ filename }}" již existuje. Prosím, zvolte jiný název souboru.';
}