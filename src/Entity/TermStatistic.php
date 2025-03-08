<?php

namespace App\Entity;

use App\Repository\TermStatisticRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TermStatisticRepository::class)]
class TermStatistic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $term = null;

    #[ORM\Column]
    private ?int $documentFrequency = null;

    #[ORM\Column(nullable: true)]
    private ?float $tfIdfValue = null;

    public function __construct(string $term, int $documentFrequency)
    {
        $this->term = $term;
        $this->documentFrequency = $documentFrequency;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerm(): ?string
    {
        return $this->term;
    }

    public function setTerm(string $term): static
    {
        $this->term = $term;

        return $this;
    }

    public function getDocumentFrequency(): ?int
    {
        return $this->documentFrequency;
    }

    public function setDocumentFrequency(int $documentFrequency): static
    {
        $this->documentFrequency = $documentFrequency;

        return $this;
    }

    public function getTfIdfValue(): ?float
    {
        return $this->tfIdfValue;
    }

    public function setTfIdfValue(?float $tfIdfValue): static
    {
        $this->tfIdfValue = $tfIdfValue;

        return $this;
    }
}
