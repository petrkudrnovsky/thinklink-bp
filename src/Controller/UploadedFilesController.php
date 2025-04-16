<?php

namespace App\Controller;

use App\Entity\FilesystemFile;
use App\Entity\User;
use App\Form\DTO\UploadFileFormData;
use App\Form\UploadFileType;
use App\Message\GetVectorEmbeddingMessage;
use App\Message\UpdateGlobalTfIdfSpaceMessage;
use App\Repository\ImageFileRepository;
use App\Repository\NoteRepository;
use App\Repository\PdfFileRepository;
use App\Service\FileHandler\FileAndArchiveHandlerCollection;
use App\Service\FileHandler\FileHandlerCollection;
use App\Service\NoteProcessingService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Webmozart\Assert\Assert;

#[Route('/files')]
#[IsGranted('ROLE_USER')]
class UploadedFilesController extends AbstractController
{
    #[Route('/', name: 'app_files_index')]
    public function index(PdfFileRepository $pdfFileRepository, ImageFileRepository $imageFileRepository): Response
    {
        return $this->render('files/index.html.twig', [
            'pdfFiles' => $pdfFileRepository->findBy(['owner' => $this->getCurrentUser()]),
            'imageFiles' => $imageFileRepository->findBy(['owner' => $this->getCurrentUser()]),
        ]);
    }

    #[Route('/upload', name: 'app_files_upload')]
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        FileAndArchiveHandlerCollection $fileHandlerCollection,
        NoteProcessingService $processingService,
    ): Response
    {
        $fileDataTransfer = new UploadFileFormData();
        $form = $this->createForm(UploadFileType::class, $fileDataTransfer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $files = $fileDataTransfer->files;

            foreach ($files as $file) {
                $fileHandler = $fileHandlerCollection->getFileHandler($file);
                $fileHandler?->upload($file);
            }

            $em->flush();

            // Global tf-idf space is updated only once after all files are uploaded to save resources
            $processingService->updateTfIdfSpace($this->getCurrentUser()->getId());

            return $this->redirectToRoute('app_files_index');
        }

        return $this->render('files/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{safeFilename}', name: 'app_files_serve')]
    #[IsGranted('view', 'file')]
    public function serveFile(FilesystemFile $file, FileHandlerCollection $fileHandlerCollection): Response
    {
        foreach($fileHandlerCollection->getFileHandlers() as $fileHandler) {
            if ($fileHandler->supportsServe($file)) {
                return $fileHandler->serve($file);
            }
        }

        throw new \LogicException('No strategy found to serve the file');
    }

    #[Route('/{safeFilename}/delete', name: 'app_files_delete')]
    public function deleteFile(Request $request, FilesystemFile $file, FilesystemOperator $defaultStorage, EntityManagerInterface $em): Response
    {
        if($this->isCsrfTokenValid('delete' . $file->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $defaultStorage->delete($file->getSafeFilename());
                $em->remove($file);
                $em->flush();
            }
            catch(FilesystemException | UnableToDeleteFile $exception) {
                throw new \RuntimeException('Cannot delete file from storage');
            }
        }

        return $this->redirectToRoute('app_files_index', [], Response::HTTP_SEE_OTHER);
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if(!$user instanceof User) {
            throw new \LogicException('User is not logged in');
        }
        return $user;
    }
}
