<?php

namespace App\Repository;

use App\Entity\TfIdfVector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TfIdfVector>
 */
class TfIdfVectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TfIdfVector::class);
    }
}
