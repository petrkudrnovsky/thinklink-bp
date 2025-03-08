<?php

namespace App\Message;

use App\Service\RelevantNotes\TfIdfMatrixStrategy\TfIdfMatrixService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateGlobalTfIdfSpaceMessageHandler
{
    public function __construct(
        private TfIdfMatrixService $tfIdfMatrixService,
    )
    {
    }

    public function __invoke(UpdateGlobalTfIdfSpaceMessage $message): void
    {
        $this->tfIdfMatrixService->updateTermStatistics();
        $this->tfIdfMatrixService->updateTfIdfVectors();
    }
}