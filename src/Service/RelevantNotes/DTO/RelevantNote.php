<?php

namespace App\Service\RelevantNotes\DTO;

use App\Entity\Note;

class RelevantNote
{
    public Note $note;
    public string $score;

    public function __construct(
        Note $note,
        ?float $score
    )
    {
        $this->note = $note;
        if ($score === null) {
            $this->score = "Ve výpočtu";
        }
        else {
            $this->score = number_format($score, 10);
        }
    }
}