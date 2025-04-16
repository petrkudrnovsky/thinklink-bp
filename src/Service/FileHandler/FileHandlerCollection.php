<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This class is a collection of FileHandlerStrategyInterface implementations. It is used to group all file handlers - $fileHandlers, so we can loop through them.
 * The current strategies are autowired and defined in the services.yaml file.
 *  Source: https://symfony.com/doc/current/service_container/tags.html#reference-tagged-services - and I changed it from tagged services to autowiring
 */
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

    public function getFileHandler(UploadedFile $file): ?FileHandlerStrategyInterface
    {
        foreach ($this->fileHandlers as $fileHandler) {
            if ($fileHandler->supports($file)) {
                return $fileHandler;
            }
        }

        return null;
    }
}