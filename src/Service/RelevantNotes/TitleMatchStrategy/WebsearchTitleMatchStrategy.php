<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

use App\Entity\Note;

/**
 *  Source: https://www.postgresql.org/docs/current/textsearch-tables.html#TEXTSEARCH-TABLES-INDEX (Example of the SQL query with the GIN index)
 *  Source (websearch_to_query): https://www.postgresql.org/docs/current/textsearch-controls.html
 *  Source (ranking with ts_rank): https://www.postgresql.org/docs/current/textsearch-controls.html#TEXTSEARCH-RANKING
 */
class WebsearchTitleMatchStrategy extends AbstractTitleMatchStrategy
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank(note.note_tsvector, websearch_to_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.owner_id = :userId AND note.note_tsvector @@ websearch_to_tsquery(:searchTerm)
            ORDER BY score DESC
        ";
    }

    protected function getSearchTerm(Note $note): string
    {
        return $note->getTitle();
    }

    public function getStrategyMethodName(): string
    {
        return 'Title match strategy with websearch_to_tsquery()';
    }
}