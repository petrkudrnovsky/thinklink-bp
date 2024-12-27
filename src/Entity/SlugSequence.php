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
    private $slug;

    #[ORM\Column]
    private ?int $slugOrder = null;

    /**
     * @param $slug
     */
    public function __construct($slug)
    {
        $this->slug = $slug;
        $this->slugOrder = 1;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlugOrder(): ?int
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
