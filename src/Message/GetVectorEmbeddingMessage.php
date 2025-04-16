<?php

namespace App\Message;

class GetVectorEmbeddingMessage
{
    public function __construct(
        private int $noteId,
    ) {
    }

    public function getNoteId(): int
    {
        return $this->noteId;
    }
}