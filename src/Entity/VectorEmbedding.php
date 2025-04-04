<?php

namespace App\Entity;

use App\Repository\VectorEmbeddingRepository;
use Doctrine\ORM\Mapping as ORM;
use Pgvector\Vector;

#[ORM\Entity(repositoryClass: VectorEmbeddingRepository::class)]
class VectorEmbedding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'vectorEmbedding', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Note $note = null;

    #[ORM\Column(type: 'vector', length: 768, nullable: true)]
    private ?Vector $geminiEmbedding = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(Note $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getGeminiEmbedding(): ?Vector
    {
        return $this->geminiEmbedding;
    }

    public function setGeminiEmbedding(Vector $geminiEmbedding): static
    {
        $this->geminiEmbedding = $geminiEmbedding;

        return $this;
    }
}
