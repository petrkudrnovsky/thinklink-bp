<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use App\Entity\ImageFile;
use App\Entity\User;
use App\Repository\ImageFileRepository;
use App\Service\Sanitizer;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;

class ImageFileStrategy implements FileHandlerStrategyInterface
{
    private static int $MAX_IMAGE_SIZE = 5242880; // 5 MB
    private static int $MAX_IMAGE_SIZE_MB = 5;
    private FilesystemOperator $storage;

    public function __construct(
        private Sanitizer $sanitizer,
        private array $allowedMimeTypes,
        private ImageFileRepository $imageFileRepository,
        private Security $security,
        FilesystemOperator $defaultStorage,
        private EntityManagerInterface $em,
    )
    {
        # Source: https://github.com/thephpleague/flysystem-bundle (needs to be named 'defaultStorage' for correct injection)
        $this->storage = $defaultStorage;
    }

    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Source: https://symfony.com/doc/current/controller/upload_file.html
     * Source: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
     * @param UploadedFile $file
     * @return void
     */
    public function upload(UploadedFile $file): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $safeFilename = $this->sanitizer->getSafeFilename($file);
        $referenceName = $this->sanitizer->getReferenceName($file);
        $mimeType = $file->getMimeType();

        $fileStream = fopen($file->getPathname(), 'r');
        if($fileStream === false) {
            throw new RuntimeException('Cannot open file for reading.');
        }
        try {
            $fileStream = fopen($file->getPathname(), 'r');
            $this->storage->writeStream($safeFilename, $fileStream);
        }
        catch (FilesystemException | UnableToWriteFile $exception) {
            fclose($fileStream);
            throw new RuntimeException('Cannot write file to storage.');
        }

        $imageFile = new ImageFile();
        $imageFile->setSafeFilename($safeFilename)
            ->setReferenceName($referenceName)
            ->setMimeType($mimeType)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setOwner($user);
        $user->addFile($imageFile);
        $this->em->persist($imageFile);
    }

    public function supportsServe(FilesystemFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Serves the file to the client and adds headers for Content-Type and Content-Disposition
     * Source: https://symfony.com/doc/current/components/http_foundation.html#serving-files
     * Source: https://symfony.com/doc/current/components/http_foundation.html#streaming-a-response
     * Source: https://dev.to/rubenrubiob/serve-a-file-stream-in-symfony-3ei3
     * Source: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
     */
    public function serve(FilesystemFile $file, string $disposition = 'inline'): Response
    {
        if(!$file instanceof ImageFile) {
            throw new \LogicException('This strategy can only serve ImageFile instances');
        }

        if($this->security->getUser() !== $file->getOwner()) {
            throw new NotFoundHttpException('Váš soubor nebyl nalezen.');
        }

        try {
            $stream = $this->storage->readStream($file->getSafeFilename());
        } catch (FilesystemException | UnableToReadFile $e) {
            throw new RuntimeException('Cannot read file from storage.');
        }

        $response = new StreamedResponse(function() use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        });

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->headers->set('Content-Disposition', $disposition === 'attachment' ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE);
        return $response;
    }

    /**
     * Validates the image
     * Source: https://symfony.com/doc/current/reference/constraints/Callback.html
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context, User $user): void
    {
        if($file->getSize() > self::$MAX_IMAGE_SIZE) {
            $context->buildViolation('Obrázek: ' . $file->getClientOriginalName() . ' je příliš velký. Maximální povolená velikost je ' . ImageFileStrategy::$MAX_IMAGE_SIZE_MB . ' MB.')
                ->atPath('files')
                ->addViolation();
            return;
        }

        $futureReferenceName = $this->sanitizer->getReferenceName($file);
        $userImageFile = $this->imageFileRepository->findOneBy(['owner' => $user, 'referenceName' => $futureReferenceName]);
        if(!$userImageFile) {
            return;
        }
        $context->buildViolation('Referenční název souboru (' . $userImageFile->getReferenceName() . ') je již použitý. Prosím pojmenujte soubor jinak.')
            ->atPath('files')
            ->addViolation();
    }
}