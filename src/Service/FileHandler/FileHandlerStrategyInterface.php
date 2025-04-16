<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

interface FileHandlerStrategyInterface
{
    /**
     * Checks if the strategy supports UploadedFile to be uploaded
     * @param UploadedFile $file
     * @return bool
     */
    public function supports(UploadedFile $file): bool;

    /**
     * Uploads the file and persists the entity
     * @param UploadedFile $file
     * @return void
     */
    public function upload(UploadedFile $file): void;

    /**
     * Validates the file, adds the violations to the context
     * Source (for the ExecutionContextInterface in the implementation): https://symfony.com/doc/current/reference/constraints/Callback.html
     * @param UploadedFile $file
     * @param ExecutionContextInterface $context
     * @param User $user
     * @return void
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context, User $user): void;

    /**
     * Checks if the strategy supports AbstractFile to be served
     * @param FilesystemFile $file
     * @return bool
     */
    public function supportsServe(FilesystemFile $file): bool;

    /**
     * Serves the file to the client
     * @param FilesystemFile $file
     * @param string $disposition (inline|attachment), default is inline
     * @return Response
     */
    public function serve(FilesystemFile $file, string $disposition = "inline"): Response;
}