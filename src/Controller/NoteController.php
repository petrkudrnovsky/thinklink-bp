<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\DTO\NoteDTO;
use App\Form\DTO\UploadNoteDTO;
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
        $noteDTO = new NoteDTO();
        $form = $this->createForm(NoteType::class, $noteDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $noteDTO->toEntity($slugGenerator->generateUniqueSlug($noteDTO->title));

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('app_note_index', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/upload', name: 'app_note_upload')]
    public function uploadNote(Request $request, EntityManagerInterface $em, SlugGenerator $slugGenerator): Response
    {
        $uploadNoteDTO = new UploadNoteDTO();
        $form = $this->createForm(UploadNoteType::class, $uploadNoteDTO);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $files = $uploadNoteDTO->files;

            foreach ($files as $file) {
                $note = new Note(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    $slugGenerator->generateUniqueSlug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                    file_get_contents($file->getPathname()),
                    $uploadNoteDTO->createdAt
                );

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

        if($note === null) {
            throw $this->createNotFoundException();
        }

        // Replace all Markdown headings in the note content with HTML headings with corresponding id attributes
        $noteUpdatedContent = $mdToHTMLHelper->convertMarkdownHeadingsToHTML($note->getContent());
        // Replace all Markdown image links in the note content with HTML img elements
        $noteUpdatedContent = $mdToHTMLHelper->convertMarkdownImagesToHTML($noteUpdatedContent);
        // Replace all Markdown link in the note content with HTML anchors
        $noteUpdatedContent = $mdToHTMLHelper->convertMarkdownLinksToHTML($noteUpdatedContent);

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

        $noteDTO = NoteDTO::createFromEntity($note);

        $form = $this->createForm(NoteType::class, $noteDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setTitle($noteDTO->title);
            $note->setContent($noteDTO->content);
            $note->setSlug($slugGenerator->generateUniqueSlug($noteDTO->title, $note));

            $entityManager->flush();

            return $this->redirectToRoute('app_note_show', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
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
