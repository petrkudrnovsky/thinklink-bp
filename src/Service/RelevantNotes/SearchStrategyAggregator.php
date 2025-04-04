<?php

namespace App\Service\RelevantNotes;

use App\Entity\Note;
use App\Entity\User;
use App\Service\RelevantNotes\DTO\RelevantNotesMethod;
use App\Service\RelevantNotes\TfIdfMatrixStrategy\CosineDistanceTfIdfMatrixStrategy;
use App\Service\RelevantNotes\TfIdfMatrixStrategy\EuclideanDistanceTfIdfMatrixStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PhraseTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainCoverDensityNormalizedTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainCoverDensityTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\WebsearchTitleMatchStrategy;
use App\Service\RelevantNotes\VectorEmbeddingStrategy\GeminiVectorEmbeddingStrategy;

class SearchStrategyAggregator
{
    public function __construct(
        private PlainTitleMatchStrategy                       $plainTitleMatchStrategy,
        private WebsearchTitleMatchStrategy                   $websearchTitleMatchStrategy,
        private PhraseTitleMatchStrategy                      $phraseTitleMatchStrategy,
        private PlainCoverDensityTitleMatchStrategy           $plainCoverDensityTitleMatchStrategy,
        private PlainCoverDensityNormalizedTitleMatchStrategy $plainCoverDensityNormalizedTitleMatchStrategy,
        private CosineDistanceTfIdfMatrixStrategy             $cosineDistanceTfIdfMatrixStrategy,
        private EuclideanDistanceTfIdfMatrixStrategy          $euclideanDistanceTfIdfMatrixStrategy,
        private GeminiVectorEmbeddingStrategy                 $geminiVectorEmbeddingStrategy,
    )
    {
    }

    public function getRelevantNotesByStrategies(Note $note, User $user): array
    {
        /** @var RelevantNotesMethod[] $relevantNotesStrategies */
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainTitleMatchStrategy->getStrategyMethodName(), $this->plainTitleMatchStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainCoverDensityTitleMatchStrategy->getStrategyMethodName(), $this->plainCoverDensityTitleMatchStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainCoverDensityNormalizedTitleMatchStrategy->getStrategyMethodName(), $this->plainCoverDensityNormalizedTitleMatchStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->websearchTitleMatchStrategy->getStrategyMethodName(), $this->websearchTitleMatchStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->phraseTitleMatchStrategy->getStrategyMethodName(), $this->phraseTitleMatchStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->cosineDistanceTfIdfMatrixStrategy->getStrategyMethodName(), $this->cosineDistanceTfIdfMatrixStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->euclideanDistanceTfIdfMatrixStrategy->getStrategyMethodName(), $this->euclideanDistanceTfIdfMatrixStrategy->findRelevantNotes($note, $user));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->geminiVectorEmbeddingStrategy->getStrategyMethodName(), $this->geminiVectorEmbeddingStrategy->findRelevantNotes($note, $user));

        return $relevantNotesStrategies;

    }

    public function getStrategies(): array
    {
        return [
            $this->plainTitleMatchStrategy,
            $this->websearchTitleMatchStrategy,
            $this->phraseTitleMatchStrategy,
            $this->plainCoverDensityTitleMatchStrategy,
            $this->plainCoverDensityNormalizedTitleMatchStrategy,
            $this->cosineDistanceTfIdfMatrixStrategy,
            $this->euclideanDistanceTfIdfMatrixStrategy,
            $this->geminiVectorEmbeddingStrategy,
        ];
    }
}