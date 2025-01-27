<?php

namespace App\Repository;

use App\Entity\FilesystemFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Given a single inheritance mapping strategy, the AbstractFileRepository class can handle ImageFile and PdfFile entities.
 * DiscriminatorColumn 'type' is used to differentiate between ImageFile('image') and PdfFile('pdf') entities.
 * @extends ServiceEntityRepository<FilesystemFile>
 */
class FilesystemFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FilesystemFile::class);
    }

    /**
     * Source: https://symfonycasts.com/screencast/doctrine-queries/where-in
     * Returns all the reference names that already exist in the database and match the reference names passed in the parameter.
     * @param string[] $referenceNames
     * @return FilesystemFile[]
     */
    public function findExistingReferenceNames(array $referenceNames): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.referenceName')
            ->where('f.referenceName IN (:referenceNames)')
            ->setParameter('referenceNames', $referenceNames)
            ->getQuery()
            ->getResult();
    }
}
