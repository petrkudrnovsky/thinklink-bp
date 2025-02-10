<?php

namespace App\Service\FileHandler;

/**
 * This class is a collection of FileHandlerStrategyInterface implementations. It is used to group all file handlers - $fileHandlers, so we can loop through them.
 * It includes the strategy for handling ZIP archives.
 * The current strategies are autowired and defined in the services.yaml file.
 * Source: https://symfony.com/doc/current/service_container/tags.html#reference-tagged-services - and I changed it from tagged services to autowiring
 */
class FileAndArchiveHandlerCollection
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