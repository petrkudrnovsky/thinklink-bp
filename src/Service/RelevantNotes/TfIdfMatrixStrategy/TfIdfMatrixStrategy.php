<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Service\RelevantNotes\SearchStrategyInterface;

class TfIdfMatrixStrategy implements SearchStrategyInterface
{

    /**
     * @inheritDoc
     */
    public function findRelevantNotes(Note $note): array
    {
        // TODO: Implement findRelevantNotes() method.
    }
}