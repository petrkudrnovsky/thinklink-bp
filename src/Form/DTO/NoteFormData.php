<?php

namespace App\Form\DTO;

use App\Entity\Note;
use App\Form\Validator\UniqueNoteTitle;
use Symfony\Component\Validator\Constraints as Assert;

class NoteFormData
{
    #[Assert\NotBlank]
    #[UniqueNoteTitle]
    public ?string $title = null;

    public ?string $content = null;

    #[Assert\NotBlank]
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function toEntity(string $slug): Note
    {
        return new Note(
            htmlspecialchars($this->title),
            $slug,
            $this->content,
            $this->createdAt
        );
    }

    public static function createFromEntity(Note $note): self
    {
        $dto = new self();
        $dto->title = $note->getTitle();
        $dto->content = $note->getContent();
        $dto->createdAt = $note->getCreatedAt();

        return $dto;
    }
}