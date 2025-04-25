<?php

namespace App\Service\RelevantNotes\TitleMatchStrategy;

use App\Entity\Note;
use App\Service\RelevantNotes\SearchStrategyInterface;

class TsqueryORTitleMatchStrategy extends AbstractTitleMatchStrategy implements SearchStrategyInterface
{
    protected function getStrategySql(): string
    {
        return "
            SELECT note.*, ts_rank_cd(note.note_tsvector, to_tsquery(:searchTerm)) AS score
            FROM note
            WHERE note.owner_id = :userId AND note.note_tsvector @@ to_tsquery(:searchTerm)
            ORDER BY score DESC
            LIMIT 10;
        ";
    }

    protected function getSearchTerm(Note $note): string
    {
        $title = $this->textPreprocessor->toLowerCase($note->getTitle());
        // Unicode aware regex: https://www.regular-expressions.info/unicode.html
        $titleOnlyLettersAndNumbers = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);
        $titleOnlyLettersAndNumbers = trim($titleOnlyLettersAndNumbers);
        $terms = preg_split('/\s+/', $titleOnlyLettersAndNumbers);

        $terms = $this->textPreprocessor->removeStopWords($terms);
        if(empty($terms)) {
            return "";
        }

        return implode(' | ', $terms);
    }

    public function getStrategyMethodName(): string
    {
        return "Metoda č. 3";
    }
}