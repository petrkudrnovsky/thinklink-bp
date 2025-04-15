<?php

namespace App\Service;

use App\Message\GetVectorEmbeddingMessage;
use App\Message\NotePreprocessMessage;
use App\Message\UpdateGlobalTfIdfSpaceMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class NoteProcessingService
{
    public function __construct(
        private MessageBusInterface $bus,
    )
    {
    }

    public function processSingleNote(int $noteId, int $userId): void
    {
        // Preprocess the note and update the global TF-IDF vectors
        # Source: https://symfony.com/doc/current/messenger.html#dispatching-the-message
        $this->bus->dispatch(new NotePreprocessMessage($noteId, $userId, true));

        // Update vector embeddings for the note
        $this->bus->dispatch(new GetVectorEmbeddingMessage($noteId));
    }

    public function processUploadedNote(int $noteId, int $userId): void
    {
        // Preprocess the note WITHOUT updating the global TF-IDF vectors
        # Source: https://symfony.com/doc/current/messenger.html#dispatching-the-message
        $this->bus->dispatch(new NotePreprocessMessage($noteId, $userId, false));
        $this->bus->dispatch(new GetVectorEmbeddingMessage($noteId));
    }

    public function updateTfIdfSpace(int $userId): void
    {
        // Update the global TF-IDF space for the user
        $this->bus->dispatch(new UpdateGlobalTfIdfSpaceMessage($userId));
    }
}