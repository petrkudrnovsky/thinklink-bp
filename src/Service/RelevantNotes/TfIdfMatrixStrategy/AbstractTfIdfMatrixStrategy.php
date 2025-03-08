<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\TermStatistic;
use App\Entity\TfIdfVector;
use App\Repository\NoteRepository;
use App\Repository\TermStatisticRepository;
use App\Repository\TfIdfVectorRepository;
use App\Service\RelevantNotes\FeatureExtraction\TextPreprocessor;
use App\Service\RelevantNotes\SearchStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pgvector\Vector;

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
    abstract public function findRelevantNotes(Note $note): array;

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