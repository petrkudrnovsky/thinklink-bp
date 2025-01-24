<?php

namespace App\Form\Validator;

use App\Repository\AbstractFileRepository;
use App\Service\Sanitizer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueFilenameValidator extends ConstraintValidator
{
    public function __construct(
        private AbstractFileRepository $abstractFileRepository,
        private Sanitizer $sanitizer)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueFilename) {
            throw new UnexpectedTypeException($constraint, UniqueFilename::class);
        }

        // If the value is empty, do not check for uniqueness - let it handle by other constraints
        if (null === $value || '' === $value) {
            return;
        }

        // If the value is not an array, throw an exception - we expect an array of files
        if (!is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        $referenceNames = [];
        foreach ($value as $file) {
            $referenceNames[] = $this->sanitizer->getReferenceName($file);
        }

        $collidingReferenceNames = $this->abstractFileRepository->findExistingReferenceNames($referenceNames);
        if($collidingReferenceNames !== []) {
            foreach($collidingReferenceNames as $collision) {
                $this->context->buildViolation("Reference '{{ referenceNames }}' už se používá. Prosím přejmenujte soubor či využijte stávající.")
                    ->setParameter('{{ referenceNames }}', $collision['referenceName'])
                    ->addViolation();
            }
        }
    }
}