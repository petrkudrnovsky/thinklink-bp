<?php

namespace App\Service\RelevantNotes\VectorEmbeddingStrategy;

use App\Entity\Note;
use App\Entity\User;

class OpenAIVectorEmbeddingStrategy extends AbstractVectorEmbeddingStrategy
{
    public function findRelevantNotes(Note $note, User $user): array
    {
        return $this->embeddingRepository->findRelevantNotesByVectorEmbeddingGemini($note->getId(), $user->getId(), $this->getStrategySql());
    }

    public function getStrategyMethodName(): string
    {
        return "OpenAI Vector Embedding Strategy";
    }

    public function getStrategySql(): string
    {
        return "
            SELECT 
                vector_embedding.*,
                (vector_embedding.open_aiembedding <=> (SELECT open_aiembedding FROM vector_embedding WHERE note_id = :noteId)) AS distance
            FROM vector_embedding
            JOIN note ON note.id = vector_embedding.note_id
            WHERE vector_embedding.note_id != :noteId AND note.owner_id = :userId
            ORDER BY distance
            LIMIT 10;
        ";
    }
}