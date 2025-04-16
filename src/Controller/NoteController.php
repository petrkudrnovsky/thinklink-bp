<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use App\Form\DTO\NoteFormData;
use App\Form\NoteType;
use App\Service\NoteProcessingService;
use App\Service\RelevantNotes\DTO\RelevantNotesMethod;
use App\Service\RelevantNotes\SearchStrategyInterface;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/note')]
#[IsGranted('ROLE_USER')]
final class NoteController extends AbstractController
{
    public function __construct(
        private iterable $relevantNotesStrategies,
    )
    {
    }

    #[Route(name: 'app_note_index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'notes' => $this->getCurrentUser()->getNotes(),
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
        $noteFormData = new NoteFormData();
        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $noteFormData->toEntity($slugGenerator->generateUniqueSlug($noteFormData->title), $this->getCurrentUser());
            $this->getCurrentUser()->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $processingService->processSingleNote($note->getId(), $this->getCurrentUser()->getId());

            return $this->redirectToRoute('app_note_index', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/new.html.twig', [
            'form' => $form,
            'files' => $this->getCurrentUser()->getFiles(),
        ]);
    }

    #[Route('/{slug}', name: 'app_note_show', methods: ['GET'])]
    #[IsGranted('view', 'note')]
    public function show(Note $note): Response
    {
        // Markdown to HTML conversion is being handled by custom Twig filter

        return $this->render('note/show.html.twig', [
            'note' => $note,
            'relevantNotesStrategies' => $this->getRelevantNotesStrategies($note),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_note_edit')]
    #[IsGranted('edit', 'note')]
    public function edit(
        Request $request,
        Note $note,
        EntityManagerInterface $entityManager,
        SlugGenerator $slugGenerator,
        NoteProcessingService $processingService,
    ): Response
    {
        $noteFormData = NoteFormData::createFromEntity($note);

        $form = $this->createForm(NoteType::class, $noteFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setTitle($noteFormData->title);
            $note->setContent($noteFormData->content);
            $note->setSlug($slugGenerator->generateUniqueSlug($noteFormData->title));

            $entityManager->flush();

            $processingService->processSingleNote($note->getId(), $this->getCurrentUser()->getId());

            return $this->redirectToRoute('app_note_show', ['slug' => $note->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('note/edit.html.twig', [
            'note' => $note,
            'form' => $form,
            'relevantNotesStrategies' => $this->getRelevantNotesStrategies($note),
            'files' => $this->getCurrentUser()->getFiles(),
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

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if(!$user instanceof User) {
            throw new \LogicException('User is not logged in');
        }
        return $user;
    }

    private function getRelevantNotesStrategies(Note $note): array
    {
        $relevantNotesStrategies = [];
        /** @var SearchStrategyInterface $strategy */
        foreach ($this->relevantNotesStrategies as $strategy) {
            $relevantNotesStrategies[] = new RelevantNotesMethod($strategy->getStrategyMethodName(), $strategy->findRelevantNotes($note, $this->getCurrentUser()));
        }
        return $relevantNotesStrategies;
    }
}
