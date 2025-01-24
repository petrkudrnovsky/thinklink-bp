<?php

namespace App\Entity;

use App\Repository\PdfFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PdfFileRepository::class)]
class PdfFile extends AbstractFile
{}
