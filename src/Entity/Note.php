<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[UniqueEntity(
    fields: ['slug', 'owner'],
    message: 'Tato poznámka je ve vaší databázi již uložena.'
)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(unique: true)]
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

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToOne(mappedBy: 'note', cascade: ['persist', 'remove'])]
    private ?VectorEmbedding $vectorEmbedding = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $editedAt = null;

    /**
     * @param string $title
     * @param string $slug
     * @param string|null $content
     * @param \DateTimeImmutable $createdAt
     * @param User $owner
     */
    public function __construct(string $title, string $slug, ?string $content, \DateTimeImmutable $createdAt, User $owner)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->editedAt = $createdAt;
        $this->owner = $owner;
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getVectorEmbedding(): ?VectorEmbedding
    {
        return $this->vectorEmbedding;
    }

    public function setVectorEmbedding(VectorEmbedding $vectorEmbedding): static
    {
        // set the owning side of the relation if necessary
        if ($vectorEmbedding->getNote() !== $this) {
            $vectorEmbedding->setNote($this);
        }

        $this->vectorEmbedding = $vectorEmbedding;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->editedAt;
    }

    public function setEditedAt(?\DateTimeInterface $editedAt): static
    {
        $this->editedAt = $editedAt;

        return $this;
    }
}
