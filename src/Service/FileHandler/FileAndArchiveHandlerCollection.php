<?php

namespace App\Service\FileHandler;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This class is a collection of FileHandlerStrategyInterface implementations. It is used to group all file handlers - $fileHandlerCollection, so we can loop through them.
 * It includes the strategy for handling ZIP archives.
 * The current strategies are autowired to FileHandlerCollection and defined in the services.yaml file.
 * Source: https://symfony.com/doc/current/service_container/tags.html#reference-tagged-services - and I changed it from tagged services to autowiring
 */
class FileAndArchiveHandlerCollection
{
    public function __construct(
        private ZipArchiveStrategy $zipArchiveStrategy,
        private FileHandlerCollection $fileHandlerCollection,
    )
    {
    }

    public function getFileHandlers(): iterable
    {
        return $this->fileHandlerCollection->getFileHandlers();
    }

    public function getFileHandler(UploadedFile $file): ?FileHandlerStrategyInterface
    {
        // ZIP archive strategy has to be out of fileHandlerCollection because of the dependency loop in ZipArchiveStrategy
        if($this->zipArchiveStrategy->supports($file)) {
            return $this->zipArchiveStrategy;
        }

        foreach ($this->fileHandlerCollection->getFileHandlers() as $fileHandler) {
            if ($fileHandler->supports($file)) {
                return $fileHandler;
            }
        }

        return null;
    }
}