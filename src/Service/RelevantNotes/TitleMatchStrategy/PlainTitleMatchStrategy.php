<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

class PlainTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank(note.note_tsvector, plainto_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.note_tsvector @@ plainto_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with plainto_tsquery()';
    }
}