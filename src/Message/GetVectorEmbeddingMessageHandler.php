<?php

namespace App\Message;

use App\Entity\VectorEmbedding;
use App\Repository\NoteRepository;
use App\Service\RelevantNotes\VectorEmbeddingStrategy\AbstractVectorEmbeddingStrategy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetVectorEmbeddingMessageHandler
{
    public function __construct(
        private iterable $vectorEmbeddingStrategies,
        private NoteRepository $noteRepository,
        private EntityManagerInterface $entityManager,
    )
    {}

    public function __invoke(GetVectorEmbeddingMessage $message): void
    {
        $note = $this->noteRepository->find($message->getNoteId());

        if($note->getVectorEmbedding() === null) {
            $vectorEmbedding = new VectorEmbedding();
            $note->setVectorEmbedding($vectorEmbedding);
            $this->entityManager->persist($vectorEmbedding);
        }

        /** @var AbstractVectorEmbeddingStrategy $strategy */
        foreach ($this->vectorEmbeddingStrategies as $strategy) {
            $strategy->createEmbedding($note);
        }

        $this->entityManager->flush();
    }
}