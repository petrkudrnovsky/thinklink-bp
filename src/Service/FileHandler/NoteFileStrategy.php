<?php

namespace App\Service\FileHandler;

use App\Entity\FilesystemFile;
use App\Entity\Note;
use App\Entity\User;
use App\Message\GetVectorEmbeddingMessage;
use App\Message\NotePreprocessMessage;
use App\Repository\NoteRepository;
use App\Service\NoteProcessingService;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class NoteFileStrategy implements FileHandlerStrategyInterface
{
    private static int $MAX_NOTE_SIZE = 5242880; // 5 MB
    public function __construct(
        // injected from services.yaml
        private array $allowedMimeTypes,
        private SlugGenerator $slugGenerator,
        private NoteRepository $noteRepository,
        private NoteProcessingService $processingService,
        private Security $security,
        private EntityManagerInterface $em,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * @inheritDoc
     */
    public function upload(UploadedFile $file): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $note = new Note(
            htmlspecialchars(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            $this->slugGenerator->generateUniqueSlug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            htmlspecialchars(file_get_contents($file->getPathname())),
            new \DateTimeImmutable(),
            $user
        );
        $user->addNote($note);
        $this->em->persist($note);
        $this->em->flush();

        $this->processingService->processUploadedNote($note->getId(), $user->getId());
    }

    /**
     * @inheritDoc
     */
    public function validate(UploadedFile $file, ExecutionContextInterface $context, User $user): void
    {
        if($file->getSize() > self::$MAX_NOTE_SIZE) {
            $context->buildViolation('Poznámka: ' . htmlspecialchars($file->getClientOriginalName()) . ' je příliš velká. Maximální povolená velikost je ' . self::$MAX_NOTE_SIZE . ' bajtů.')
                ->atPath('files')
                ->addViolation();
            return;
        }

        $futureNoteTitle = htmlspecialchars(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if(strlen($futureNoteTitle) > 255) {
            $context->buildViolation('Poznámka: ' . $futureNoteTitle . ' má příliš dlouhý název. Maximální povolená délka je 255 znaků.')
                ->atPath('files')
                ->addViolation();
            return;
        }

        $note = $this->noteRepository->findOneBy(['title' => $futureNoteTitle, 'owner' => $user]);
        if($note) {
            $context->buildViolation('Název poznámky (' . $note->getTitle() . ') je již použitý. Prosím pojmenujte soubor jinak.')
                ->atPath('files')
                ->addViolation();
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsServe(FilesystemFile $file): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function serve(FilesystemFile $file, string $disposition = "inline"): Response
    {
        throw new \LogicException('This strategy does not support serving files');
    }
}