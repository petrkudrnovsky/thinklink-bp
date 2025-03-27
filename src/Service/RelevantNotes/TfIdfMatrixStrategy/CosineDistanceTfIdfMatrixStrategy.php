<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\User;

class CosineDistanceTfIdfMatrixStrategy extends AbstractTfIdfMatrixStrategy
{

    /**
     * @inheritDoc
     */
    public function getStrategyMethodName(): string
    {
        return "TfIdfMatrixStrategy: Cosine Distance";
    }

    /**
     * @inheritDoc
     * Cosine distance has the <=> operator
     */
    public function getStrategySql(): string
    {
        return "
            SELECT 
                *,
                (tf_idf_vector.vector <=> (SELECT vector FROM tf_idf_vector WHERE note_id = :noteId)) AS distance
            FROM tf_idf_vector
            WHERE note_id != :noteId
            ORDER BY distance
            LIMIT 10;
        ";
    }

    public function findRelevantNotes(Note $note, User $user): array
    {
        return $this->tfIdfVectorRepository->findRelevantNotesByVectorSimilarity($note->getId(), $this->getStrategySql(), true);
    }
}