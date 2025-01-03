<?php

namespace App\Form\DTO;

use App\Form\Validator\UniqueFilename;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadImageDTO
{
    #[Assert\NotBlank(message: 'Prosím, nahrajte alespoň jeden obrázek.')]
    #[Assert\All([
        new Assert\File(
            maxSize: '5M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/svg+xml'],
            maxSizeMessage: 'Maximální povolená velikost souboru je 5 MB.',
            mimeTypesMessage: 'Soubor {{ name }} není podporován. Aplikace podporuje pouze formáty {{ types }}.'
        )
    ])]
    #[UniqueFilename]
    /**
     * @var array<UploadedFile>|null $files
     */
    public ?array $files = null;
}