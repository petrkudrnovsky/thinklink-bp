<?php

namespace App\Message;

use App\Service\RelevantNotes\TfIdfMatrixStrategy\TfIdfMatrixStrategy;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateGlobalTfIdfSpaceMessageHandler
{
    public function __construct(
        private TfIdfMatrixStrategy $tfIdfMatrixStrategy,
    )
    {
    }

    public function __invoke(UpdateGlobalTfIdfSpaceMessage $message): void
    {
        $this->tfIdfMatrixStrategy->updateTermStatistics();
        $this->tfIdfMatrixStrategy->updateTfIdfVectors();
    }
}