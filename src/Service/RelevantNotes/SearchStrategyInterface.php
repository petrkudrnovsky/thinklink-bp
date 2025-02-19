<?php

namespace App\Service\RelevantNotes;

use App\Entity\Note;
use App\Service\RelevantNotes\DTO\RelevantNote;

interface SearchStrategyInterface
{
    /**
     * @param Note $note
     * @return RelevantNote[]
     */
    public function findRelevantNotes(Note $note): array;
}