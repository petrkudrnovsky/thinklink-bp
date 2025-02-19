<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

class PhraseTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank(note.note_tsvector, phraseto_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.note_tsvector @@ phraseto_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with phraseto_tsquery()';
    }
}