<?php

namespace App\Repository;

use App\Entity\SlugSequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SlugSequence>
 */
class SlugSequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlugSequence::class);
    }

    public function findOneBySlug(string $slug): ?SlugSequence
    {
        return $this->createQueryBuilder('seq')
            ->andWhere('seq.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
