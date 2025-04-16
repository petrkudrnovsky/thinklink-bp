<?php

namespace App\Form\DTO;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserEditFormData
{
    #[Assert\NotBlank(message: 'Vyplňte prosím e-mail')]
    #[Assert\Email(message: 'E-mail není platný')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Vyplňte prosím jméno')]
    public ?string $name = null;

    public ?bool $isAdmin = null;
    public function __construct(
        ?string $email = null,
        ?string $name = null,
        ?bool $isAdmin = false,
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->isAdmin = $isAdmin;
    }

    public function toEntity(): User
    {
        $user = new User();
        $user->setEmail($this->email);
        $user->setName($this->name);
        if ($this->isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        return $user;
    }

    public static function createFromEntity(User $user): self
    {
        return new self(
            $user->getEmail(),
            $user->getName(),
            in_array('ROLE_ADMIN', $user->getRoles(), true)
        );
    }
}