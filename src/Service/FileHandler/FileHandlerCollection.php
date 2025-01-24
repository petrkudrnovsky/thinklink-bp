<?php

namespace App\Service\FileHandler;

class FileHandlerCollection
{
    public function __construct(
        private iterable $fileHandlers
    )
    {
    }

    public function getFileHandlers(): iterable
    {
        return $this->fileHandlers;
    }
}