<?php

namespace App\Service\RelevantNotes;

use App\Entity\Note;
use App\Entity\User;
use App\Service\RelevantNotes\DTO\RelevantNote;

interface SearchStrategyInterface
{
    /**
     * @param Note $note
     * @param User $user
     * @return RelevantNote[]
     */
    public function findRelevantNotes(Note $note, User $user): array;

    public function getStrategyMethodName(): string;
}