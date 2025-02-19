<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

use App\Entity\Note;
use App\Repository\NoteRepository;
use App\Service\RelevantNotes\SearchStrategyInterface;

abstract class AbstractTitleMatchStrategy implements SearchStrategyInterface
{
    public function __construct(
        private NoteRepository $noteRepository
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function findRelevantNotes(Note $note): array
    {
        return $this->noteRepository->findRelevantNotesByFulltextSearch($note->getTitle(), $this->getStrategySql());
    }

    abstract protected function getStrategySql(): string;

    abstract public function getStrategyMethodName(): string;
}