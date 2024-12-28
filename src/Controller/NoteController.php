<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\UploadNoteType;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use App\Service\MarkdownToHTMLHelper;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

#[Route('/note')]
final class NoteController extends AbstractController
{
    #[Route(name: 'app_note_index')]
    public function index(NoteRepository $noteRepository): Response
    {
        return $this->render('note/index.html.twig', [
            'notes' => $noteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_note_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SlugGenerator $slugGenerator): Response
    {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setSlug($slugGenerator->generateUniqueSlug($note->getTitle()));

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/upload', name: 'app_note_upload')]
    public function uploadNote(Request $request, EntityManagerInterface $em, SlugGenerator $slugGenerator): Response
    {
        $form = $this->createForm(UploadNoteType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && !$form->isValid()) {
            dd($form->getErrors());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $files */
            $files = $form->get('files')->getData();

            if (empty($files)) {
                $form->get('files')->addError(new FormError('Prosím, nahrajte alespoň jeden soubor.'));
            }

            foreach ($files as $file) {
                $note = new Note();
                $note->setTitle(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $note->setSlug($slugGenerator->generateUniqueSlug($note->getTitle()));
                $note->setContent(file_get_contents($file->getPathname()));

                $em->persist($note);
            }

            $em->flush();

            return $this->redirectToRoute('app_note_index');
        }

        return $this->render('note/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{slug}', name: 'app_note_show', methods: ['GET'])]
    public function show(string $slug, NoteRepository $noteRepository, MarkdownToHTMLHelper $mdToHTMLHelper): Response
    {
        $note = $noteRepository->findBySlug($slug);

        // replace all markdown link in the note content with HTML anchors
        $noteUpdatedContent = $mdToHTMLHelper->convertMarkdownLinksToHTML($note->getContent());

        return $this->render('note/show.html.twig', [
            'note' => $note,
            'noteContent' => $noteUpdatedContent,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_note_edit')]
    public function edit(Request $request, string $slug, NoteRepository $noteRepository, EntityManagerInterface $entityManager, SlugGenerator $slugGenerator): Response
    {
        $note = $noteRepository->findBySlug($slug);
        if($note === null) {
            throw $this->createNotFoundException();
        }

        $noteTitle = $note->getTitle();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($noteTitle !== $note->getTitle()) {
                $note->setSlug($slugGenerator->generateUniqueSlug($note->getTitle()));
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_note_delete', methods: ['POST'])]
    public function delete(Request $request, string $slug, NoteRepository $noteRepository, EntityManagerInterface $entityManager): Response
    {
        $note = $noteRepository->findBySlug($slug);
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
