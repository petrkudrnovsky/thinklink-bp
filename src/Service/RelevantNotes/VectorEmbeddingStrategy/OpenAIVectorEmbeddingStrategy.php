<?php

namespace App\Service\RelevantNotes\VectorEmbeddingStrategy;

use App\Entity\Note;
use App\Entity\User;
use Pgvector\Vector;

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

    public function createEmbedding(Note $note): void
    {
        $response = $this->httpClient->request(
            'POST',
            'https://api.openai.com/v1/embeddings',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                ],
                'json' => [
                    'model' => 'text-embedding-3-small',
                    'input' => $note->getTitle() . ' ' . $note->getContent(),
                ]
            ]
        );

        $data = $response->toArray();
        $values = $data['data'][0]['embedding'] ?? [];

        $note->getVectorEmbedding()->setOpenAIEmbedding(new Vector($values));
    }
}