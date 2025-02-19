<?php

namespace App\Service\RelevantNotes;

use App\Entity\Note;
use App\Service\RelevantNotes\DTO\RelevantNotesMethod;
use App\Service\RelevantNotes\TitleMatchStrategy\PhraseTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainCoverDensityNormalizedTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainCoverDensityTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\PlainTitleMatchStrategy;
use App\Service\RelevantNotes\TitleMatchStrategy\WebsearchTitleMatchStrategy;

class SearchStrategyAggregator
{
    public function __construct(
        private PlainTitleMatchStrategy $plainTitleMatchStrategy,
        private WebsearchTitleMatchStrategy $websearchTitleMatchStrategy,
        private PhraseTitleMatchStrategy $phraseTitleMatchStrategy,
        private PlainCoverDensityTitleMatchStrategy $plainCoverDensityTitleMatchStrategy,
        private PlainCoverDensityNormalizedTitleMatchStrategy $plainCoverDensityNormalizedTitleMatchStrategy,
    )
    {
    }

    public function getRelevantNotesByStrategies(Note $note): array
    {
        /** @var RelevantNotesMethod[] $relevantNotesStrategies */
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainTitleMatchStrategy->getStrategyMethodName(), $this->plainTitleMatchStrategy->findRelevantNotes($note));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainCoverDensityTitleMatchStrategy->getStrategyMethodName(), $this->plainCoverDensityTitleMatchStrategy->findRelevantNotes($note));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->plainCoverDensityNormalizedTitleMatchStrategy->getStrategyMethodName(), $this->plainCoverDensityNormalizedTitleMatchStrategy->findRelevantNotes($note));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->websearchTitleMatchStrategy->getStrategyMethodName(), $this->websearchTitleMatchStrategy->findRelevantNotes($note));
        $relevantNotesStrategies[] = new RelevantNotesMethod($this->phraseTitleMatchStrategy->getStrategyMethodName(), $this->phraseTitleMatchStrategy->findRelevantNotes($note));

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
        ];
    }
}