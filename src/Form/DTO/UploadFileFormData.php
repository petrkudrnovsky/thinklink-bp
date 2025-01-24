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
            maxSizeMessage: 'Maximální povolená velikost souboru je {{ limit }} {{ suffix }}. Nahraný soubor má {{ size }} {{ suffix }}.',
            // using extensions over mimeTypes as it is more secure - see https://symfony.com/doc/current/reference/constraints/File.html#mimetypes
            extensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'],
            extensionsMessage: 'Tento typ souboru ({{ extension }}) není podporován. Podporované typy souborů jsou: {{ extensions }}.'
        )
    ])]
    #[UniqueFilename]
    /**
     * @var array<UploadedFile>|null $files
     */
    public ?array $files = null;
}