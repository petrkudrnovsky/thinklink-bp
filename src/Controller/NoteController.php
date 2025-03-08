<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\DTO\NoteFormData;
use App\Form\NoteType;
use App\Message\NotePreprocessMessage;
use App\Repository\NoteRepository;
use App\Service\RelevantNotes\SearchStrategyAggregator;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

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
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SlugGenerator $slugGenerator,
        MessageBusInterface $bus
    ): Response
    {
        $noteFormData = new NoteFormData();
        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $noteFormData->toEntity($slugGenerator->generateUniqueSlug($noteFormData->title));

            $entityManager->persist($note);
            $entityManager->flush();

            // Preprocess the note and update the global TF-IDF vectors
            # Source: https://symfony.com/doc/current/messenger.html#dispatching-the-message
            $bus->dispatch(new NotePreprocessMessage($note->getId(), true));

            return $this->redirectToRoute('app_note_index', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_note_show', methods: ['GET'])]
    public function show(Note $note, SearchStrategyAggregator $strategyAggregator): Response
    {
        // Markdown to HTML conversion is being handled by custom Twig filter

        return $this->render('note/show.html.twig', [
            'note' => $note,
            'noteContent' => $note->getContent(),
            'relevantNotesStrategies' => $strategyAggregator->getRelevantNotesByStrategies($note),
            'map' => $note->getTfIdfVector()->getTermFrequencies(),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_note_edit')]
    public function edit(
        Request $request,
        Note $note,
        EntityManagerInterface $entityManager,
        SlugGenerator $slugGenerator,
        SearchStrategyAggregator $strategyAggregator,
        MessageBusInterface $bus,
    ): Response
    {
        $noteFormData = NoteFormData::createFromEntity($note);

        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setTitle($noteFormData->title);
            $note->setContent($noteFormData->content);
            $note->setSlug($slugGenerator->generateUniqueSlug($noteFormData->title, $note));

            $entityManager->flush();

            // Preprocess the note and update the global TF-IDF vectors
            # Source: https://symfony.com/doc/current/messenger.html#dispatching-the-message
            $bus->dispatch(new NotePreprocessMessage($note->getId(), true));

            return $this->redirectToRoute('app_note_show', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
            'relevantNotesStrategies' => $strategyAggregator->getRelevantNotesByStrategies($note),
        ]);
    }

    #[Route('/{slug}', name: 'app_note_delete', methods: ['POST'])]
    public function delete(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
