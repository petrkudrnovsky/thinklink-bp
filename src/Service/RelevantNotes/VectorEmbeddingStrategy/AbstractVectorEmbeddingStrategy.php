<?php

namespace App\Service\RelevantNotes\VectorEmbeddingStrategy;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\VectorEmbeddingRepository;
use App\Service\RelevantNotes\SearchStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pgvector\Vector;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractVectorEmbeddingStrategy implements SearchStrategyInterface
{
    public function __construct(
        protected VectorEmbeddingRepository $embeddingRepository,
        protected HttpClientInterface $httpClient,
        protected EntityManagerInterface $em,
    )
    {}

    /**
     * @inheritDoc
     */
    abstract public function findRelevantNotes(Note $note, User $user): array;

    /**
     * Get the method name of the strategy.
     * @return string
     */
    abstract public function getStrategyMethodName(): string;

    /**
     * Get the SQL query for vector embedding strategy.
     * Source: https://github.com/pgvector/pgvector?tab=readme-ov-file#querying (pgvector GitHub documentation)
     * @return string
     */
    abstract public function getStrategySql(): string;

    abstract public function createEmbedding(Note $note): void;
}