<?php

namespace App\Controller;

use App\Entity\FilesystemFile;
use App\Form\DTO\UploadFileFormData;
use App\Form\UploadFileType;
use App\Repository\FilesystemFileRepository;
use App\Service\FileHandler\FileHandlerCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/files')]
class UploadedFilesController extends AbstractController
{
    #[Route('/', name: 'app_files_index')]
    public function index(FilesystemFileRepository $filesystemFileRepository): Response
    {
        // todo: separate the files by type and render them in different sections
        return $this->render('files/index.html.twig', [
            'files' => $filesystemFileRepository->findAll(),
        ]);
    }

    #[Route('/upload', name: 'app_files_upload')]
    public function upload(Request $request, EntityManagerInterface $em, FileHandlerCollection $fileHandlerCollection): Response
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
