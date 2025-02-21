<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[UniqueEntity(
    fields: ['title', 'slug'],
    message: 'Tato poznámka je již v databázi uložena.'
)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::ASCII_STRING, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * This is a one-to-one relationship with the TfIdfVector entity. Each Note has one TfIdfVector.
     */
    #[ORM\OneToOne(mappedBy: 'note', cascade: ['persist', 'remove'])]
    private ?TfIdfVector $tfIdfVector = null;

    /**
     * @param string $title
     * @param string $slug
     * @param string|null $content
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(string $title, string $slug, ?string $content, \DateTimeImmutable $createdAt)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->content = $content;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTfIdfVector(): ?TfIdfVector
    {
        return $this->tfIdfVector;
    }

    public function setTfIdfVector(?TfIdfVector $tfIdfVector): static
    {
        // unset the owning side of the relation if necessary
        if ($tfIdfVector === null && $this->tfIdfVector !== null) {
            $this->tfIdfVector->setNote(null);
        }

        // set the owning side of the relation if necessary
        if ($tfIdfVector !== null && $tfIdfVector->getNote() !== $this) {
            $tfIdfVector->setNote($this);
        }

        $this->tfIdfVector = $tfIdfVector;

        return $this;
    }
}
