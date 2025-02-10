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

    /**
     * Reference name is for referencing the file in the notes. It reflects the original filename.
     * @param UploadedFile $file
     * @return string
     */
    public function getReferenceName(UploadedFile $file): string
    {
        # Source: https://symfony.com/doc/current/controller/upload_file.html
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $referenceName = htmlspecialchars($originalFilename);
        return $referenceName . '.' . $file->guessExtension();
    }

    /**
     * Safe filename is for storing the file on the server. It is unique and safe to use.
     * @param UploadedFile $file
     * @return string
     */
    public function getSafeFilename(UploadedFile $file): string
    {
        # Source: https://symfony.com/doc/current/controller/upload_file.html
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }
}