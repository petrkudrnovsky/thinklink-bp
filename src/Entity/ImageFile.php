<?php

namespace App\Entity;

use App\Repository\ImageFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageFileRepository::class)]
class ImageFile extends FilesystemFile
{}
