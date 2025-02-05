<?php

namespace App\Entity;

use App\Repository\SlugSequenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlugSequenceRepository::class)]
class SlugSequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ASCII_STRING, unique: true)]
    private string $slug;

    #[ORM\Column]
    private int $slugOrder;

    /**
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        $this->slug = $slug;
        $this->slugOrder = 1;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlugOrder(): int
    {
        return $this->slugOrder;
    }

    public function setSlugOrder(int $slugOrder): static
    {
        $this->slugOrder = $slugOrder;

        return $this;
    }

    public function incrementSlugOrder(): static
    {
        $this->slugOrder++;

        return $this;
    }
}
