<?php

namespace App\Service\RelevantNotes\DTO;

class RelevantNotesMethod
{
    public function __construct(
        private string $methodName,
        /**
         * @var RelevantNote[]
         */
        private array $relevantNotes
    )
    {
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return RelevantNote[]
     */
    public function getRelevantNotes(): array
    {
        return $this->relevantNotes;
    }
}