<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use App\Form\DTO\NoteFormData;
use App\Form\NoteType;
use App\Message\GetVectorEmbeddingMessage;
use App\Message\NotePreprocessMessage;
use App\Service\NoteProcessingService;
use App\Service\RelevantNotes\SearchStrategyAggregator;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Webmozart\Assert\Assert;

#[Route('/note')]
#[IsGranted('ROLE_USER')]
final class NoteController extends AbstractController
{
    #[Route(name: 'app_note_index')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        Assert::nullOrIsInstanceOf($user, User::class);

        return $this->render('home/index.html.twig', [
            'notes' => $user->getNotes(),
        ]);
    }

    #[Route('/new', name: 'app_note_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SlugGenerator $slugGenerator,
        NoteProcessingService $processingService,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        Assert::nullOrIsInstanceOf($user, User::class);

        $noteFormData = new NoteFormData();
        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $noteFormData->toEntity($slugGenerator->generateUniqueSlug($noteFormData->title), $user);
            $user->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $processingService->processSingleNote($note->getId(), $user->getId());

            return $this->redirectToRoute('app_note_index', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'form' => $form,
            'files' => $user->getFiles(),
        ]);
    }

    #[Route('/{slug}', name: 'app_note_show', methods: ['GET'])]
    #[IsGranted('view', 'note')]
    public function show(Note $note, SearchStrategyAggregator $strategyAggregator): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        Assert::nullOrIsInstanceOf($user, User::class);

        // Markdown to HTML conversion is being handled by custom Twig filter

        return $this->render('note/show.html.twig', [
            'note' => $note,
            'noteContent' => $note->getContent(),
            'files' => $user->getFiles(),
            'relevantNotesStrategies' => $strategyAggregator->getRelevantNotesByStrategies($note, $user),
            'map' => $note->getTfIdfVector()->getTermFrequencies(),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_note_edit')]
    #[IsGranted('edit', 'note')]
    public function edit(
        Request $request,
        Note $note,
        EntityManagerInterface $entityManager,
        SlugGenerator $slugGenerator,
        SearchStrategyAggregator $strategyAggregator,
        NoteProcessingService $processingService,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        Assert::nullOrIsInstanceOf($user, User::class);

        $noteFormData = NoteFormData::createFromEntity($note);

        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setTitle($noteFormData->title);
            $note->setContent($noteFormData->content);
            $note->setSlug($slugGenerator->generateUniqueSlug($noteFormData->title));

            $entityManager->flush();

            $processingService->processSingleNote($note->getId(), $user->getId());

            return $this->redirectToRoute('app_note_show', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
            'relevantNotesStrategies' => $strategyAggregator->getRelevantNotesByStrategies($note, $user),
            'files' => $user->getFiles(),
        ]);
    }

    #[Route('/{slug}', name: 'app_note_delete', methods: ['POST'])]
    #[IsGranted('delete', 'note')]
    public function delete(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
