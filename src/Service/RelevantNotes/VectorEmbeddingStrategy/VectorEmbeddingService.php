<?php

namespace App\Service\RelevantNotes\VectorEmbeddingStrategy;

use App\Entity\Note;
use App\Entity\VectorEmbedding;
use Pgvector\Vector;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VectorEmbeddingService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function getVectorEmbeddingGemini(Note $note): Vector
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
        return new Vector($values);
    }

    public function getVectorEmbeddingOpenAI(Note $note): Vector
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
        return new Vector($values);
    }
}