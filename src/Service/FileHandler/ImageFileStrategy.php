<?php

namespace App\Service\FileHandler;

use App\Entity\AbstractFile;
use App\Entity\ImageFile;
use App\Service\FileHandler\FileHandlerStrategyInterface;
use App\Service\Sanitizer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageFileStrategy implements FileHandlerStrategyInterface
{
    public function __construct(
        private Sanitizer $sanitizer,
        #[Autowire('%kernel.project_dir%/public/uploads/images')] private string $uploadDirectory
    )
    {
    }

    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif']);
    }

    /**
     * Uploads the file to the filesystem
     * Source: https://symfony.com/doc/current/controller/upload_file.html
     * @param UploadedFile $file
     * @return ImageFile
     */
    public function upload(UploadedFile $file): ImageFile
    {
        $safeFilename = $this->sanitizer->getSafeFilename($file);
        $referenceName = $this->sanitizer->getReferenceName($file);

        try {
            $file->move($this->uploadDirectory, $safeFilename);
        } catch (FileException $e) {
            // todo: handle exception if something goes wrong with the file upload
        }

        $imageFile = new ImageFile();
        $imageFile->setSafeFilename($safeFilename)
            ->setReferenceName($referenceName)
            ->setMimeType($file->getMimeType())
            ->setCreatedAt(new \DateTimeImmutable());
        return $imageFile;
    }

    /**
     * Serves the file to the client and adds headers for Content-Type and Content-Disposition
     * Source: https://symfony.com/doc/current/components/http_foundation.html#serving-files
     * @param AbstractFile $file
     * @return Response
     */
    public function serve(AbstractFile $file): Response
    {
        if(!$file instanceof ImageFile) {
            throw new \LogicException('This strategy can only serve ImageFile instances');
        }

        $path = $this->uploadDirectory . '/' . $file->getSafeFilename();
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getReferenceName()
        );
        return $response;
    }
}