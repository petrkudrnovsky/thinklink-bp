<?php

namespace App\Form\Validator;

use App\Entity\User;
use App\Service\FileHandler\FileAndArchiveHandlerCollection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UploadedFileConstraintValidator extends ConstraintValidator
{
    public function __construct(
        private FileAndArchiveHandlerCollection $fileHandlerCollection,
        // injected from services.yaml
        private array $allowedMimeTypes,
        private Security $security,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UploadedFileConstraint) {
            throw new UnexpectedTypeException($constraint, UploadedFileConstraint::class);
        }

        // If the value is empty, do not check - let it handle by other constraints
        if (null === $value || '' === $value) {
            return;
        }

        // If the value is not an array, throw an exception - we expect an array of files
        if (!is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var UploadedFile $file */
        foreach ($value as $file) {
            $handled = false;

            $fileHandler = $this->fileHandlerCollection->getFileHandler($file);
            if ($fileHandler) {
                $fileHandler->validate($file, $this->context);
                $handled = true;
            }

            if(!$handled) {
                $this->context->buildViolation("Soubor '{{ filename }}' není podporován. Podporované formáty jsou: {{ supportedFormats }}")
                    ->setParameter('{{ filename }}', $file->getClientOriginalName())
                    ->setParameter('{{ supportedFormats }}', implode(', ', $this->allowedMimeTypes))
                    ->addViolation();
            }
        }
    }
}