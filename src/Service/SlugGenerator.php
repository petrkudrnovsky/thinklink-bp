<?php

namespace App\Service;

use App\Entity\SlugSequence;
use App\Repository\NoteRepository;
use App\Repository\SlugSequenceRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugGenerator
{
    public function __construct(
        private SluggerInterface $slugger,
        private NoteRepository $noteRepository,
        private SlugSequenceRepository $slugSequenceRepository,
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * Generates a unique slug for a given title. If the slug already exists, it appends a sequence number to it starting from 1.
     * @param string $title
     * @return string
     */
    public function generateUniqueSlug(string $title): string
    {
        $slug = $this->slugger->slug($title);

        $note = $this->noteRepository->findBySlug($slug);
        if($note === null) {
            return $slug;
        } else {
            $slugSequence = $this->slugSequenceRepository->findOneBySlug($slug);
            if ($slugSequence === null) {
                $slugSequence = new SlugSequence($slug);
                $slug = $slug . '-' . $slugSequence->getSlugOrder();
                $slugSequence->incrementSlugOrder();
                $this->em->persist($slugSequence);
            } else {
                $slugOrder = $slugSequence->getSlugOrder();
                $slug = $slug . '-' . $slugOrder;
                $slugSequence->incrementSlugOrder();
            }
            $this->em->flush();
        }

        return $slug;
    }
}