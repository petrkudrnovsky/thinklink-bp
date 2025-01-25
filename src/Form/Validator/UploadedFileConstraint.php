<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UploadedFileConstraint extends Constraint
{
    public string $message = 'Problém s nahrávaným souborem.';
}