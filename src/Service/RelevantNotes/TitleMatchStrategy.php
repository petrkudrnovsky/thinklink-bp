<?php

namespace App\Service\RelevantNotes;

use App\Entity\Note;
use App\Repository\NoteRepository;

class TitleMatchStrategy implements SearchStrategyInterface
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
        return $this->noteRepository->findRelevantNotesByFulltextSearch($note->getTitle());
    }
}