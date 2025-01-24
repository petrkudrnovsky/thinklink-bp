<?php

namespace App\Form\DTO;

use App\Form\Validator\UniqueFilename;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadFileFormData
{
    #[Assert\NotBlank(message: 'Prosím, nahrajte alespoň jeden soubor.')]
    #[Assert\All([
        new Assert\File(
            maxSize: '10M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf', 'application/x-pdf'],
            maxSizeMessage: 'Maximální povolená velikost souboru je 10 MB.',
            mimeTypesMessage: 'Soubor {{ name }} není podporován. Aplikace podporuje pouze formáty {{ types }}.'
        )
    ])]
    #[UniqueFilename]
    /**
     * @var array<UploadedFile>|null $files
     */
    public ?array $files = null;
}