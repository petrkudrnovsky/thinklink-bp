<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

class WebsearchTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank(note.note_tsvector, websearch_to_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.note_tsvector @@ websearch_to_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with websearch_to_tsquery()';
    }
}