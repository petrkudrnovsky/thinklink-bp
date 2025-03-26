<?php

namespace App\Controller;

use App\Entity\FilesystemFile;
use App\Form\DTO\UploadFileFormData;
use App\Form\UploadFileType;
use App\Message\UpdateGlobalTfIdfSpaceMessage;
use App\Repository\ImageFileRepository;
use App\Repository\NoteRepository;
use App\Repository\PdfFileRepository;
use App\Service\FileHandler\FileAndArchiveHandlerCollection;
use App\Service\FileHandler\FileHandlerCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/files')]
#[IsGranted('ROLE_USER')]
class UploadedFilesController extends AbstractController
{
    #[Route('/', name: 'app_files_index')]
    public function index(PdfFileRepository $pdfFileRepository, ImageFileRepository $imageFileRepository, NoteRepository $noteRepository): Response
    {
        return $this->render('files/index.html.twig', [
            'pdfFiles' => $pdfFileRepository->findAll(),
            'imageFiles' => $imageFileRepository->findAll(),
            'notes' => $noteRepository->findAll(),
        ]);
    }

    #[Route('/upload', name: 'app_files_upload')]
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        FileAndArchiveHandlerCollection $fileHandlerCollection,
        MessageBusInterface $bus,
    ): Response
    {
        $fileDataTransfer = new UploadFileFormData();
        $form = $this->createForm(UploadFileType::class, $fileDataTransfer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $files = $fileDataTransfer->files;

            foreach ($files as $file) {
                foreach($fileHandlerCollection->getFileHandlers() as $fileHandler) {
                    if ($fileHandler->supports($file)) {
                        $fileHandler->upload($file, $em);
                        break;
                    }
                }
            }

            $em->flush();

            // Global tf-idf space is updated only once after all files are uploaded to save resources
            $bus->dispatch(new UpdateGlobalTfIdfSpaceMessage());

            return $this->redirectToRoute('app_files_index');
        }

        return $this->render('files/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{referenceName}', name: 'app_files_serve')]
    public function serveFile(FilesystemFile $file, FileHandlerCollection $fileHandlerCollection): Response
    {
        foreach($fileHandlerCollection->getFileHandlers() as $fileHandler) {
            if ($fileHandler->supportsServe($file)) {
                return $fileHandler->serve($file);
            }
        }

        throw new \LogicException('No strategy found to serve the file');
    }
}
