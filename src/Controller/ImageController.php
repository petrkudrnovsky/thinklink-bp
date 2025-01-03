<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\DTO\UploadImageDTO;
use App\Form\UploadImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $imageDTO = new UploadImageDTO();
        $form = $this->createForm(UploadImageType::class, $imageDTO);
        $form->handleRequest($request);

        /*if($form->isSubmitted() && !$form->isValid()) {
            dd($form->getErrors(true));
        }*/

        if($form->isSubmitted() && $form->isValid()) {
            $files = $imageDTO->files;
            foreach ($files as $file) {
                $image = new Image($file->getClientOriginalName(), $file->getMimeType(), file_get_contents($file->getPathname()));
                $em->persist($image);
            }

            $em->flush();
            return $this->redirectToRoute('app_image_index');
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form,
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
