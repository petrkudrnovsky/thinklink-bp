<?php

namespace App\Message;

class UpdateGlobalTfIdfSpaceMessage
{
    public function __construct(
        private int $userId,
    )
    {
    }

    /**
     * It is better to use an entity's id instead of the entity itself: https://symfony.com/doc/current/messenger.html#doctrine-entities-in-messages
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}