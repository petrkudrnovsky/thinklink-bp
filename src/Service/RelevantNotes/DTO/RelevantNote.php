<?php

namespace App\Service\RelevantNotes\DTO;

use App\Entity\Note;

class RelevantNote
{
    public function __construct(
        public Note $note,
        public float $score
    )
    {
    }
}