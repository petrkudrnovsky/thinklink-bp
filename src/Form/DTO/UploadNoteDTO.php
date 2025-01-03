<?php

namespace App\Form\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadNoteDTO
{
    #[Assert\NotBlank(message: 'Prosím, nahrajte alespoň jeden soubor.')]
    #[Assert\All([
        new Assert\File(
            maxSize: '5M',
            mimeTypes: ['text/plain', 'text/markdown'],
            maxSizeMessage: 'Maximální povolená velikost souboru je 5 MB.',
            mimeTypesMessage: 'Soubor {{ name }} není podporován. Aplikace podporuje pouze formáty {{ types }}.'
        )
    ])]
    /**
     * @var array<UploadedFile>|null $files
     */
    public ?array $files = null;
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}