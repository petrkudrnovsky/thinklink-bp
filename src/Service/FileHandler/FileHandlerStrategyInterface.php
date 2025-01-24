<?php

namespace App\Service\FileHandler;

use App\Entity\AbstractFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface FileHandlerStrategyInterface
{
    /**
     * Checks if the strategy supports UploadedFile to be uploaded
     * @param UploadedFile $file
     * @return bool
     */
    public function supportsUpload(UploadedFile $file): bool;

    /**
     * Uploads the file to the filesystem
     * @param UploadedFile $file
     * @return AbstractFile
     */
    public function upload(UploadedFile $file): AbstractFile;

    /**
     * Checks if the strategy supports AbstractFile to be served
     * @param AbstractFile $file
     * @return bool
     */
    public function supportsServe(AbstractFile $file): bool;

    /**
     * Serves the file to the client
     * @param AbstractFile $file
     * @param string $disposition (inline|attachment), default is inline
     * @return Response
     */
    public function serve(AbstractFile $file, string $disposition = "inline"): Response;
}