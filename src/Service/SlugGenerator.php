<?php

namespace App\Service;

use App\Repository\NoteRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class SlugGenerator
{
    public function __construct(
        private SluggerInterface       $slugger,
        private NoteRepository         $noteRepository,
    )
    {
    }

    /**
     * Generates a slug for a given heading.
     * @param string $heading
     * @return string
     */
    public function generateHeadingSlug(string $heading): string
    {
        return $this->slugger->slug($heading);
    }

    /**
     * Generates a unique slug for a given title.
     * If the slug already exists, it finds a different unique number to append to it.
     * @param string $title
     * @return string
     */
    public function generateUniqueSlug(string $title): string
    {
        $sluggedTitle = $this->slugger->slug($title);
        $slug = $sluggedTitle . '-' . uniqid();
        while($this->noteRepository->findBySlug($slug) !== null) {
            $slug = $sluggedTitle . '-' . uniqid();
        }

        return $slug;
    }
}