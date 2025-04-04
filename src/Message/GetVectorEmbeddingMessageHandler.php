<?php

namespace App\Message;

use App\Entity\VectorEmbedding;
use App\Repository\NoteRepository;
use App\Service\RelevantNotes\VectorEmbeddingStrategy\VectorEmbeddingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetVectorEmbeddingMessageHandler
{
    public function __construct(
        private NoteRepository $noteRepository,
        private VectorEmbeddingService $vectorEmbeddingService,
        private EntityManagerInterface $entityManager,
    )
    {}

    public function __invoke(GetVectorEmbeddingMessage $message): void
    {
        $note = $this->noteRepository->find($message->getNoteId());

        $vector = $this->vectorEmbeddingService->getVectorEmbeddingGemini($note);

        $vectorEmbedding = $note->getVectorEmbedding();
        if($vectorEmbedding === null) {
            $vectorEmbedding = new VectorEmbedding();
            $note->setVectorEmbedding($vectorEmbedding);
            $this->entityManager->persist($vectorEmbedding);
        }
        $vectorEmbedding->setGeminiEmbedding($vector);
        $this->entityManager->flush();
    }
}