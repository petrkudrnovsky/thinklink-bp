<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\TfIdfVectorRepository;
use App\Service\RelevantNotes\SearchStrategyInterface;

abstract class AbstractTfIdfMatrixStrategy implements SearchStrategyInterface
{
    public function __construct(
        protected TfIdfVectorRepository $tfIdfVectorRepository,
    )
    {
    }

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
     * Get the SQL query for the TF-IDF matrix strategy.
     * Source: https://github.com/pgvector/pgvector?tab=readme-ov-file#querying (pgvector GitHub documentation)
     * @return string
     */
    abstract public function getStrategySql(): string;
}