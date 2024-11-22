<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteController extends AbstractController
{
    #[Route('/note', name: 'app_note')]
    public function index(NoteRepository $repository): Response
    {
        $notes = $repository->findAll();
        return $this->render('note/index.html.twig', [
            'notes' => $notes,
        ]);
    }

    #[Route('/note/new', name: 'app_note_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(NoteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $files */
            $files = $form->get('files')->getData();

            foreach ($files as $file) {
                /*if ($file->getMimeType() !== 'text/markdown') {
                    continue;
                }*/
                $note = new Note();
                $note->setTitle(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $note->setContent(file_get_contents($file->getPathname()));

                $em->persist($note);
            }

            $em->flush();

            return $this->redirectToRoute('app_note');
        }

        return $this->render('note/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/note/{id}', name: 'app_note_show')]
    public function show(int $id, NoteRepository $repository): Response
    {
        $note = $repository->findById($id);

        if (!$note) {
            throw $this->createNotFoundException('Note not found.');
        }

        return $this->render('note/show.html.twig', [
            'note' => $note,
        ]);
    }

    #[Route('/note/{id}/edit', name: 'app_note_edit')]
    public function edit(int $id): Response
    {
        return $this->render('note/edit.html.twig', [
            'controller_name' => 'NoteController',
            'id' => $id,
        ]);
    }

    #[Route('/note/{id}', name: 'app_note_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        // to-do: delete note
        return $this->redirectToRoute('app_note');
    }
}
