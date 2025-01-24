<?php

namespace App\Service\FileHandler;

use App\Entity\AbstractFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface FileHandlerStrategyInterface
{
    public function supports(UploadedFile $file): bool;
    public function upload(UploadedFile $file): AbstractFile;
    public function serve(AbstractFile $file): Response;
}