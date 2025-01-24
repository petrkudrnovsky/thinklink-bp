<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class Sanitizer
{
    public function __construct(
        private SluggerInterface $slugger,
    )
    {
    }

    public function getReferenceName(UploadedFile $file): string
    {
        // source: https://symfony.com/doc/current/controller/upload_file.html
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $referenceName = $this->slugger->slug($originalFilename);
        return $referenceName . '.' . $file->guessExtension();
    }

    public function getSafeFilename(UploadedFile $file): string
    {
        // source: https://symfony.com/doc/current/controller/upload_file.html
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }
}