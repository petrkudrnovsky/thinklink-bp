<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

class PlainCoverDensityNormalizedTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank_cd(note.note_tsvector, plainto_tsquery(:searchTerm), 4) AS score
            FROM note
            WHERE note.note_tsvector @@ plainto_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with plainto_tsquery() with cover density with normalization 4 (harmonic distance between lexemes)';
    }
}