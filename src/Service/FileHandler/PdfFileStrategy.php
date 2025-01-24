<?php

namespace App\Service\FileHandler;

use App\Entity\AbstractFile;
use App\Entity\ImageFile;
use App\Service\Sanitizer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PdfFileStrategy implements FileHandlerStrategyInterface
{
    public function __construct(
        private Sanitizer $sanitizer,
        private string $uploadDirectory,
        private array $allowedMimeTypes
    )
    {
    }

    public function supportsUpload(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Source: https://symfony.com/doc/current/controller/upload_file.html
     */
    public function upload(UploadedFile $file): AbstractFile
    {
        $safeFilename = $this->sanitizer->getSafeFilename($file);
        $referenceName = $this->sanitizer->getReferenceName($file);
        $mimeType = $file->getMimeType();

        try {
            $file->move($this->uploadDirectory, $safeFilename);
        } catch (FileException $e) {
            // todo: handle exception if something goes wrong with the file upload
        }

        $imageFile = new ImageFile();
        $imageFile->setSafeFilename($safeFilename)
            ->setReferenceName($referenceName)
            ->setMimeType($mimeType)
            ->setCreatedAt(new \DateTimeImmutable());
        return $imageFile;
    }

    public function supportsServe(AbstractFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Serves the file to the client and adds headers for Content-Type and Content-Disposition
     * Source: https://symfony.com/doc/current/components/http_foundation.html#serving-files
     */
    public function serve(AbstractFile $file, string $disposition = 'inline'): Response
    {
        if(!$file instanceof ImageFile) {
            throw new \LogicException('This strategy can only serve ImageFile instances');
        }

        $path = $this->uploadDirectory . '/' . $file->getSafeFilename();
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContentDisposition(
            $disposition === 'attachment' ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getReferenceName()
        );
        return $response;
    }
}