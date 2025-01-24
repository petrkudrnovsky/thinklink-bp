<?php

namespace App\Entity;

use App\Repository\AbstractFileRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Source: https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/inheritance-mapping.html#single-table-inheritance
 * Source https://symfony.com/doc/current/reference/constraints/UniqueEntity.html
 * Information on Single Table Inheritance: https://martinfowler.com/eaaCatalog/singleTableInheritance.html + Chapter 12 of Patterns of Enterprise Application Architecture by Martin Fowler
 * @ORM\Entity(repositoryClass=AbstractFileRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "image" = ImageFile::class,
 *     "pdf" = PdfFile::class,
 * })
 */
#[ORM\Entity(repositoryClass: AbstractFileRepository::class)]
#[UniqueEntity(fields: ['referenceName'], message: 'Jméno souboru: {{ value }} je již použito.')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'image' => ImageFile::class,
    'pdf' => PdfFile::class,
])]
abstract class AbstractFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $safeFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $referenceName = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSafeFilename(): ?string
    {
        return $this->safeFilename;
    }

    public function setSafeFilename(string $safeFilename): static
    {
        $this->safeFilename = $safeFilename;

        return $this;
    }

    public function getReferenceName(): ?string
    {
        return $this->referenceName;
    }

    public function setReferenceName(string $referenceName): static
    {
        $this->referenceName = $referenceName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
