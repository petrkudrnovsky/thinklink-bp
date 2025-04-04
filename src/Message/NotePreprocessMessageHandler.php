<?php

namespace App\Message;

use App\Repository\NoteRepository;
use App\Service\RelevantNotes\TfIdfMatrixStrategy\TfIdfMatrixService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class NotePreprocessMessageHandler
{
    public function __construct(
        private NoteRepository              $noteRepository,
        private TfIdfMatrixService          $tfIdfMatrixService,
        private MessageBusInterface         $bus,
    )
    {
    }

    public function __invoke(NotePreprocessMessage $message): void
    {
        $note = $this->noteRepository->find($message->getNoteId());
        if ($note === null) {
            return;
        }

        $this->tfIdfMatrixService->preprocessNote($note);

        if($message->isUpdateGlobalTfIdfSpace()){
            # Source: https://symfony.com/doc/current/messenger.html#dispatching-the-message
            $this->bus->dispatch(new UpdateGlobalTfIdfSpaceMessage($message->getUserId()));
        }
    }
}