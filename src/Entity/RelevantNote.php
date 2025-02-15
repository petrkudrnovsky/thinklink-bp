<?php

namespace App\Entity;

class RelevantNote
{
    public function __construct(
        public Note $note,
        public float $score
    )
    {
    }
}