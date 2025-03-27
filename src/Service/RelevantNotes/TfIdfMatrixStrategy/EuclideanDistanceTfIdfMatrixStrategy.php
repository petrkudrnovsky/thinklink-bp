<?php

namespace App\Service\RelevantNotes\TfIdfMatrixStrategy;

use App\Entity\Note;
use App\Entity\User;
use App\Service\RelevantNotes\TfIdfMatrixStrategy\AbstractTfIdfMatrixStrategy;

class EuclideanDistanceTfIdfMatrixStrategy extends AbstractTfIdfMatrixStrategy
{

    /**
     * @inheritDoc
     */
    public function getStrategyMethodName(): string
    {
        return "TF-IDF Matrix Strategy: Euclidean Distance";
    }

    /**
     * @inheritDoc
     * Euclidean distance has the <-> operator (L2 distance)
     */
    public function getStrategySql(): string
    {
        return "
            SELECT 
                *,
                (tf_idf_vector.vector <-> (SELECT vector FROM tf_idf_vector WHERE note_id = :noteId)) AS distance
            FROM tf_idf_vector
            WHERE note_id != :noteId
            ORDER BY distance
            LIMIT 10;
        ";
    }

    public function findRelevantNotes(Note $note, User $user): array
    {
        return $this->tfIdfVectorRepository->findRelevantNotesByVectorSimilarity($note->getId(), $this->getStrategySql(), false);
    }
}