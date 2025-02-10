<?php

namespace App\Form\DTO;

use App\Form\Validator\UploadedFileConstraint;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadFileFormData
{
    #[Assert\NotBlank(message: 'Prosím, nahrajte alespoň jeden soubor.')]
    #[UploadedFileConstraint]
    /**
     * @var array<UploadedFile>|null $files
     */
    public ?array $files = null;
}