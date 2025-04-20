<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use App\Entity\User;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mime\MimeTypes;
use ZipArchive;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ZipArchiveStrategy implements FileHandlerStrategyInterface
{
    private static int $MAX_ZIP_SIZE = 10485760; // 10 MB
    private static int $MAX_FILE_COUNT = 100;

    public function __construct(
        // injected from services.yaml
        private array $allowedMimeTypes,
        private string $uploadDirectory,
        private FileHandlerCollection $fileHandlerCollection,
        private Security $security,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * @inheritDoc
     */
    public function upload(UploadedFile $file): void
    {
        # Source: https://www.php.net/manual/en/ziparchive.open.php
        $zip = new ZipArchive();
        $result = $zip->open($file->getPathname());
        if($result !== true) {
            $zip->close();
            throw new RuntimeException('Nelze otevřít ZIP soubor '. htmlspecialchars($file->getPathname()) . ' - chyba: ' . $result);
        }

        $extractedFiles = $this->getExtractedFiles($zip);

        foreach($extractedFiles as $extractedFile) {
            $fileHandler = $this->fileHandlerCollection->getFileHandler($extractedFile);
            $fileHandler?->upload($extractedFile);
        }

        $this->cleanUpTemporaryFiles($extractedFiles);
        $zip->close();
    }

    /**
     * @param ZipArchive $zip
     * @return UploadedFile[]
     * @throws RuntimeException
     */
    private function getExtractedFiles(ZipArchive $zip): array
    {
        /** @var UploadedFile[] $extractedFiles */
        $extractedFiles = [];

        try {
            # Source: https://www.php.net/manual/en/class.ziparchive.php
            for($i = 0; $i < $zip->numFiles; ++$i) {
                # Source: https://www.php.net/manual/en/ziparchive.statindex.php
                $status = $zip->statIndex($i);
                // Skip pointers to directories, focus on files inside the directories
                if($status === false || str_ends_with($status['name'], '/')) {
                    continue;
                }

                $filePath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $status['name'];

                # Source: https://www.php.net/manual/en/ziparchive.extractto.php
                if($zip->extractTo($this->uploadDirectory, $status['name'])) {
                    # Source: https://www.php.net/manual/en/class.splfileinfo.php (SPL = standard PHP library)
                    $fileInfo = new SplFileInfo($filePath);
                    # Source: https://symfony.com/doc/current/components/mime.html#guessing-the-mime-type
                    $mimeTypes = new MimeTypes();
                    $mimeType = $mimeTypes->guessMimeType($fileInfo->getPathname());
                    $extractedFiles[] = new UploadedFile($fileInfo->getPathname(), $fileInfo->getFilename(), $mimeType, null, true);
                }
                else {
                    throw new RuntimeException('Nelze extrahovat soubor ' . htmlspecialchars($status['name']));
                }
            }
        }
        catch (RuntimeException $e) {
            $this->cleanUpTemporaryFiles($extractedFiles);
            throw $e;
        }
        return $extractedFiles;
    }

    /**
     * @param UploadedFile[] $files
     * @return void
     */
    private function cleanUpTemporaryFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if($file->getSize() > self::$MAX_ZIP_SIZE) {
            $context->buildViolation('ZIP soubor '. htmlspecialchars($file->getClientOriginalName()) . ' je příliš velký. Maximální povolená velikost je ' . self::$MAX_ZIP_SIZE . ' bajtů.')
                ->atPath('files')
                ->addViolation();
            return;
        }

        # Source: https://www.php.net/manual/en/ziparchive.open.php
        $zip = new ZipArchive();
        $result = $zip->open($file->getPathname());
        if($result !== true) {
            $context->buildViolation('Nelze otevřít ZIP soubor '. htmlspecialchars($file->getPathname()) . ' - chyba: ' . $result)
                ->atPath('files')
                ->addViolation();
            $zip->close();
            return;
        }
        # Source: https://www.php.net/manual/en/class.ziparchive.php#ziparchive.props.numfiles
        if($zip->numFiles > self::$MAX_FILE_COUNT) {
            $context->buildViolation('ZIP soubor '. htmlspecialchars($file->getPathname()) . ' obsahuje více než povolené maximum '. self::$MAX_FILE_COUNT . ' souborů.')
                ->atPath('files')
                ->addViolation();
            $zip->close();
            return;
        }

        $extractedFiles = $this->getExtractedFiles($zip);

        // If the file is not supported, we will just skip it (it won't be uploaded as well since we are using the same supports() function)
        foreach($extractedFiles as $extractedFile) {
            foreach($this->fileHandlerCollection->getFileHandlers() as $fileHandler) {
                if ($fileHandler->supports($extractedFile)) {
                    $fileHandler->validate($extractedFile, $context, $user);
                    break;
                }
            }
        }

        $this->cleanUpTemporaryFiles($extractedFiles);
        $zip->close();
    }

    /**
     * @inheritDoc
     */
    public function supportsServe(FilesystemFile $file): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function serve(FilesystemFile $file, string $disposition = "inline"): Response
    {
        throw new \LogicException('This strategy does not support serving files');
    }
}