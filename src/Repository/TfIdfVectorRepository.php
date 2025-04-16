<?php

namespace App\Repository;

use App\Entity\TfIdfVector;
use App\Service\RelevantNotes\DTO\RelevantNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    /**
     * Source (NativeQuery): https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html
     * @param int $noteId
     * @param int $userId
     * @param string $sql
     * @return array
     */
    public function findRelevantNotesByVectorSimilarity(int $noteId, int $userId, string $sql): array
    {
        # Source: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html#resultsetmappingbuilder
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(TfIdfVector::class, 'tfIdfVector');
        # Source: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html#scalar-results
        $rsm->addScalarResult('distance', 'distance');

        $result = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('noteId', $noteId)
            ->setParameter('userId', $userId)
            ->getResult();

        // Mapping to RelevantNote, so I can display the score in the template
        return array_map(function($row) {
            /** @var TfIdfVector $tfIdfVector */
            $tfIdfVector = $row[0];
            $note = $tfIdfVector->getNote();

            return new RelevantNote($note, $row['distance']);
        }, $result);
    }
}
