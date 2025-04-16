<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Service\RelevantNotes\DTO\RelevantNote;
use ContainerEFE2ixM\getDoctrine_CacheClearResultCommandService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * @return Note[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['createdAt' => 'DESC']);
    }

    /**
     * Finds a Note by its title. If more Notes with the same title exist, it will return the first one. (todo: handle multiple Notes with the same name or make them unique through the whole application)
     * @param string $title
     * @return Note|null
     */
    public function findOneByName(string $title): ?Note
    {
        return $this->createQueryBuilder('note')
            ->andWhere('note.title = :title')
            ->setParameter('title', $title)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $slug
     * @return ?Note
     */
    public function findBySlug(string $slug): ?Note
    {
        return $this->createQueryBuilder('note')
            ->andWhere('note.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return ?Note
     */
    public function findById(int $id): ?Note
    {
        return $this->find($id);
    }

    /**
     * Finds Notes that contain the search term in their content.
     * Source: https://www.postgresql.org/docs/current/textsearch-tables.html#TEXTSEARCH-TABLES-INDEX (Example of the SQL query with the GIN index)
     * Source (plainto_query or websearch_to_query): https://www.postgresql.org/docs/current/textsearch-controls.html
     * Source (ranking with ts_rank): https://www.postgresql.org/docs/current/textsearch-controls.html#TEXTSEARCH-RANKING
     * Source (NativeQuery): https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html
     * @param string $searchTerm
     * @param string $sql - given SQL by the actual strategy
     * @param User $user
     * @return RelevantNote[]
     */
    public function findRelevantNotesByFulltextSearch(string $searchTerm, string $sql, User $user): array
    {
        # Source: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html#resultsetmappingbuilder
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Note::class, 'note');
        # Source: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html#scalar-results
        $rsm->addScalarResult('score', 'score');

        $result = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('searchTerm', $searchTerm)
            ->setParameter('userId', $user->getId())
            ->getResult();

        // Mapping to RelevantNote, so I can display the score in the template
        return array_map(function($row) {
            return new RelevantNote($row[0], $row['score']);
        }, $result);
    }
}
