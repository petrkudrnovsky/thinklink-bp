<?php

namespace App\Message;

class NotePreprocessMessage
{
    public function __construct(
        private int $noteId,
        private bool $updateGlobalTfIdfSpace = false,
    )
    {
    }

    /**
     * It is better to use an entity's id instead of the entity itself: https://symfony.com/doc/current/messenger.html#doctrine-entities-in-messages
     * @return int
     */
    public function getNoteId(): int
    {
        return $this->noteId;
    }

    /**
     * If true, the global tf-idf space will be updated AFTER the note is preprocessed to avoid racing conditions. Default is false.
     * It is separated, because the global tf-idf space update is a heavy operation and should be done only when necessary - if user uploads a lot of new notes (via ZIP archive), global tf-idf space should be updated only once.
     * However, if the user uploads a single note, the global tf-idf space should be updated after each note is preprocessed - to ensure that the global tf-idf space is up-to-date.
     * @return bool
     */
    public function isUpdateGlobalTfIdfSpace(): bool
    {
        return $this->updateGlobalTfIdfSpace;
    }
}