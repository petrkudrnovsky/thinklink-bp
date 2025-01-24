<?php

namespace App\Controller;

use App\Entity\AbstractFile;
use App\Form\DTO\UploadFileFormData;
use App\Form\UploadFileType;
use App\Repository\AbstractFileRepository;
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
    public function index(AbstractFileRepository $abstractFileRepository): Response
    {
        // todo: separate the files by type and render them in different sections
        return $this->render('upload/index.html.twig', [
            'files' => $abstractFileRepository->findAll(),
        ]);
    }

    #[Route('/upload', name: 'app_files_upload')]
    public function upload(Request $request, EntityManagerInterface $em, FileHandlerCollection $collection): Response
    {
        $fileDataTransfer = new UploadFileFormData();
        $form = $this->createForm(UploadFileType::class, $fileDataTransfer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $files = $fileDataTransfer->files;

            foreach ($files as $file) {
                foreach($collection as $strategy) {
                    if ($strategy->supports($file)) {
                        $file = $strategy->upload($file);
                        break;
                    }
                }

                $em->persist($file);
            }

            $em->flush();
            return $this->redirectToRoute('app_files_index');
        }

        return $this->render('upload/upload.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{referenceName}', name: 'app_files_serve')]
    public function serveFile(AbstractFile $file, FileHandlerCollection $collection): Response
    {
        foreach($collection as $strategy) {
            if ($strategy->supports($file)) {
                return $strategy->serve($file);
            }
        }

        throw new \LogicException('No strategy found to serve the file');
    }
}
