<?php

namespace App\Service;

use App\Entity\Note;
use App\Entity\SlugSequence;
use App\Repository\NoteRepository;
use App\Repository\SlugSequenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class SlugGenerator
{
    public function __construct(
        private SluggerInterface       $slugger,
        private NoteRepository         $noteRepository,
        private SlugSequenceRepository $slugSequenceRepository,
        private EntityManagerInterface $em
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
     * If the slug already exists, it appends a sequence number to it starting from 1.
     * If the original note is provided, it will not append a sequence number if the slug is the same as the original note's slug.
     * @param string $title
     * @param Note|null $originalNote
     * @return string
     */
    public function generateUniqueSlug(string $title, ?Note $originalNote = null): string
    {
        $slug = $this->slugger->slug($title);

        $note = $this->noteRepository->findBySlug($slug);
        if($note === null) {
            return $slug;
        }

        if ($originalNote !== null && $note->getId() === $originalNote->getId()) {
            return $slug;
        }

        $slugSequence = $this->slugSequenceRepository->findOneBySlug($slug);

        if($slugSequence === null) {
            $slugSequence = new SlugSequence($slug);
        }
        $slug = $slug . '-' . $slugSequence->getSlugOrder();
        $slugSequence->incrementSlugOrder();
        $this->em->persist($slugSequence);
        $this->em->flush();

        return $slug;
    }
}