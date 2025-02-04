<?php

namespace App\Form\Validator;

use App\Repository\FilesystemFileRepository;
use App\Repository\NoteRepository;
use App\Service\Sanitizer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueNoteTitleValidator extends ConstraintValidator
{
    public function __construct(
        private NoteRepository $noteRepository
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueNoteTitle) {
            throw new UnexpectedTypeException($constraint, UniqueNoteTitle::class);
        }

        // If the value is empty, do not check for uniqueness - let it handle by other constraints
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $note = $this->noteRepository->findOneBy(['title' => $value]);
        if($note) {
            $this->context->buildViolation('Název "{{ noteTitle }}" je již použitý. Prosím pojmenujte soubor jinak.')
                ->setParameter('{{ noteTitle }}', $note->getTitle())
                ->addViolation();
        }
    }
}