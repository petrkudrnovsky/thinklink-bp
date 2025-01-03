<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $filename;

    #[ORM\Column(length: 255)]
    private string $mimeType;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $data;

    /**
     * @param $data
     * @param string $mimeType
     * @param string $filename
     */
    public function __construct(string $filename, string $mimeType, mixed $data)
    {
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->data = $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
