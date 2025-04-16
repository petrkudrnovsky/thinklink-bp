<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

use App\Entity\Note;
use App\Entity\User;
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
    public function findRelevantNotes(Note $note, User $user): array
    {
        return $this->noteRepository->findRelevantNotesByFulltextSearch($note->getTitle(), $this->getStrategySql(), $user);
    }

    abstract protected function getStrategySql(): string;

    abstract public function getStrategyMethodName(): string;
}