<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

/**
 *  Source: https://www.postgresql.org/docs/current/textsearch-tables.html#TEXTSEARCH-TABLES-INDEX (Example of the SQL query with the GIN index)
 *  Source (plainto_query): https://www.postgresql.org/docs/current/textsearch-controls.html
 *  Source (ranking with ts_rank): https://www.postgresql.org/docs/current/textsearch-controls.html#TEXTSEARCH-RANKING
 */
class PlainTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank(note.note_tsvector, plainto_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.owner_id = :userId AND note.note_tsvector @@ plainto_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with plainto_tsquery()';
    }
}