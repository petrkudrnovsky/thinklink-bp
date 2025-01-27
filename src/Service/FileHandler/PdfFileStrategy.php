<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use App\Entity\PdfFile;
use App\Repository\PdfFileRepository;
use App\Service\Sanitizer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;

class PdfFileStrategy implements FileHandlerStrategyInterface
{
    private static int $MAX_PDF_SIZE = 10000000;
    public function __construct(
        private Sanitizer $sanitizer,
        // injected from services.yaml
        private string $uploadDirectory,
        private array $allowedMimeTypes,
        private PdfFileRepository $pdfFileRepository,
    )
    {
    }

    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Source: https://symfony.com/doc/current/controller/upload_file.html
     * @param UploadedFile $file
     * @param EntityManagerInterface $em
     * @return void
     */
    public function upload(UploadedFile $file, EntityManagerInterface $em): void
    {
        $safeFilename = $this->sanitizer->getSafeFilename($file);
        $referenceName = $this->sanitizer->getReferenceName($file);
        $mimeType = $file->getMimeType();

        try {
            $file->move($this->uploadDirectory, $safeFilename);
        } catch (FileException $e) {
            // todo: handle exception if something goes wrong with the file upload
        }

        $pdfFile = new PdfFile();
        $pdfFile->setSafeFilename($safeFilename)
            ->setReferenceName($referenceName)
            ->setMimeType($mimeType)
            ->setCreatedAt(new \DateTimeImmutable());
        $em->persist($pdfFile);
    }

    public function supportsServe(FilesystemFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Serves the file to the client and adds headers for Content-Type and Content-Disposition
     * Source: https://symfony.com/doc/current/components/http_foundation.html#serving-files
     */
    public function serve(FilesystemFile $file, string $disposition = 'inline'): Response
    {
        if(!$file instanceof PdfFile) {
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

    /**
     * Validates the PDF file
     * Source: https://symfony.com/doc/current/reference/constraints/Callback.html
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context): void
    {
        if($file->getSize() > PdfFileStrategy::$MAX_PDF_SIZE) {
            $context->buildViolation('PDF soubor: ' . $file->getClientOriginalName() . ' je příliš velký. Maximální povolená velikost je ' . PdfFileStrategy::$MAX_PDF_SIZE . ' bajtů.')
                ->atPath('files')
                ->addViolation();
            return;
        }

        $futureReferenceName = $this->sanitizer->getReferenceName($file);
        $pdfFile = $this->pdfFileRepository->findOneBy(['referenceName' => $futureReferenceName]);
        if(!$pdfFile) {
            return;
        }
        $context->buildViolation('Referenční název souboru (' . $pdfFile->getReferenceName() . ') je již použitý. Prosím pojmenujte soubor jinak.')
            ->atPath('files')
            ->addViolation();
    }
}