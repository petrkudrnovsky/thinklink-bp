<?php

namespace App\Entity;

use App\Repository\TfIdfVectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Pgvector\Vector;

#[ORM\Entity(repositoryClass: TfIdfVectorRepository::class)]
class TfIdfVector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * This is a one-to-one relationship with the Note entity. Each Note has one TfIdfVector.
     */
    #[ORM\OneToOne(inversedBy: 'tfIdfVector', cascade: ['persist', 'remove'])]
    private ?Note $note = null;

    #[ORM\Column(type: 'vector', length: 100, nullable: true)]
    private ?Vector $vector = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getVector(): ?Vector
    {
        return $this->vector;
    }

    public function setVector(?Vector $vector): static
    {
        $this->vector = $vector;

        return $this;
    }
}
