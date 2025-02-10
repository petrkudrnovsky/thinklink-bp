<?php

namespace App\Form\Validator;

use App\Form\DTO\NoteFormData;
use App\Repository\NoteRepository;
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
        if(!$constraint instanceof UniqueNoteTitle) {
            throw new UnexpectedTypeException($constraint, UniqueNoteTitle::class);
        }

        // If the value is empty, do not check for uniqueness - let it handle by other constraints
        if(null === $value || '' === $value) {
            return;
        }

        if(!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        /**
         * @var NoteFormData $noteFormData
         */
        $noteFormData = $this->context->getObject();
        if(!$noteFormData instanceof NoteFormData) {
            throw new UnexpectedValueException($noteFormData, NoteFormData::class);
        }

        $note = $this->noteRepository->findOneBy(['title' => $value]);
        if($note) {
            if($noteFormData->getNoteId() === $note->getId()) {
                return;
            }

            $this->context->buildViolation('Název "{{ noteTitle }}" je již použitý. Prosím pojmenujte soubor jinak.')
                ->setParameter('{{ noteTitle }}', $note->getTitle())
                ->addViolation();
        }
    }
}