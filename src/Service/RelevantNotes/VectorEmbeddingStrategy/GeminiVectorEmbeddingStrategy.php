<?php

namespace App\Service\RelevantNotes\VectorEmbeddingStrategy;

use App\Entity\Note;
use App\Entity\User;
use Pgvector\Vector;

class GeminiVectorEmbeddingStrategy extends AbstractVectorEmbeddingStrategy
{
    public function findRelevantNotes(Note $note, User $user): array
    {
        return $this->embeddingRepository->findRelevantNotesByVectorEmbeddingGemini($note->getId(), $user->getId(), $this->getStrategySql());
    }

    public function getStrategyMethodName(): string
    {
        return "Metoda č. 1";
    }

    public function getStrategySql(): string
    {
        return "
            SELECT 
                vector_embedding.*,
                (vector_embedding.gemini_embedding <=> (SELECT gemini_embedding FROM vector_embedding WHERE note_id = :noteId)) AS distance
            FROM vector_embedding
            JOIN note ON note.id = vector_embedding.note_id
            WHERE vector_embedding.note_id != :noteId AND note.owner_id = :userId
            ORDER BY distance
            LIMIT 10;
        ";
    }

    public function createEmbedding(Note $note): void
    {
        $response = $this->httpClient->request(
            'POST',
            'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key=' . $_ENV['GOOGLE_API_KEY'],
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => [
                        'parts' => [
                            [
                                'text' => $note->getTitle() . ' ' . $note->getContent(),
                            ]
                        ],
                    ],
                    'taskType' => 'SEMANTIC_SIMILARITY'
                ],
            ]
        );

        $data = $response->toArray();
        $values = $data['embedding']['values'] ?? [];

        $note->getVectorEmbedding()->setGeminiEmbedding(new Vector($values));
    }
}