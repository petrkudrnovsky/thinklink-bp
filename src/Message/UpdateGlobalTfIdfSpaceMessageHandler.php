<?php

namespace App\Message;

use App\Repository\UserRepository;
use App\Service\RelevantNotes\TfIdfMatrixStrategy\TfIdfMatrixService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateGlobalTfIdfSpaceMessageHandler
{
    public function __construct(
        private TfIdfMatrixService $tfIdfMatrixService,
        private UserRepository $userRepository,
    )
    {
    }

    public function __invoke(UpdateGlobalTfIdfSpaceMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());

        $this->tfIdfMatrixService->updateTermStatistics($user);
        $this->tfIdfMatrixService->updateTfIdfVectors($user);
    }
}