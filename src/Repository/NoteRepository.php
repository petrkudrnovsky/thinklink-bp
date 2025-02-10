<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
