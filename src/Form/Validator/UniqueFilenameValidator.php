<?php

namespace App\Form\Validator;

use App\Repository\AbstractFileRepository;
use App\Repository\ImageRepository;
use App\Service\FileHandler\FileHandlerCollection;
use App\Service\Sanitizer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueFilenameValidator extends ConstraintValidator
{
    private AbstractFileRepository $abstractFileRepository;
    private Sanitizer $sanitizer;

    /**
     * @param AbstractFileRepository $abstractFileRepository
     * @param Sanitizer $sanitizer
     */
    public function __construct(AbstractFileRepository $abstractFileRepository, Sanitizer $sanitizer)
    {
        $this->abstractFileRepository = $abstractFileRepository;
        $this->sanitizer = $sanitizer;
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
                $this->context->buildViolation("The reference name '{{ referenceNames }}' is already in use, please rename the file.")
                    ->setParameter('{{ referenceNames }}', $collision->getReferenceName())
                    ->addViolation();
            }
        }
    }
}