<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\UploadImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/image')]
final class ImageController extends AbstractController
{
    #[Route(name: 'app_image_index')]
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/upload', name: 'app_image_upload')]
    public function upload(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UploadImageType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && !$form->isValid()) {
            dd($form->getErrors());
        }

        if($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('files')->getData();

            // todo: handle duplicate file names - It cannot be

            if (empty($files)) {
                $form->get('files')->addError(new FormError('Prosím, nahrajte alespoň jeden soubor.'));
            }

            foreach ($files as $file) {
                $image = new Image();
                $image->setFilename($file->getClientOriginalName());
                $image->setMimeType($file->getMimeType());
                $image->setData(file_get_contents($file->getPathname()));
                $em->persist($image);
            }

            $em->flush();

            return $this->redirectToRoute('app_image_index');
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_image_serve')]
    public function serveImage(int $id, ImageRepository $imageRepository): Response
    {
        $image = $imageRepository->find($id);
        if($image === null) {
            throw $this->createNotFoundException("Image not found.");
        }

        return new Response(stream_get_contents($image->getData()), Response::HTTP_OK, [
            'Content-Type' => $image->getMimeType(),
        ]);
    }
}
