<?php

namespace App\Form\Validator;

use App\Repository\ImageRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueFilenameValidator extends ConstraintValidator
{
    private ImageRepository $imageRepository;

    /**
     * @param ImageRepository $imageRepository
     */
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
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

        foreach ($value as $file) {
            if ($this->imageRepository->findOneBy(['filename' => $file->getClientOriginalName()])) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ filename }}', $file->getClientOriginalName())
                    ->addViolation();
            }
        }
    }
}