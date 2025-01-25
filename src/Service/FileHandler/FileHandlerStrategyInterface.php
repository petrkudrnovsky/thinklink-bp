<?php

namespace App\Service\FileHandler;

use App\Entity\AbstractFile;
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
     * Uploads the file to the filesystem
     * @param UploadedFile $file
     * @param EntityManagerInterface $em
     * @return void
     */
    public function upload(UploadedFile $file, EntityManagerInterface $em): void;

    /**
     * Validates the file, adds the violations to the context
     * Source (for the ExecutionContextInterface in the implementation): https://symfony.com/doc/current/reference/constraints/Callback.html
     * @param UploadedFile $file
     * @param ExecutionContextInterface $context
     * @return void
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context): void;

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