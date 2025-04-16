<?php

namespace App\Form\DTO;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserCreateFormData
{
    #[Assert\NotBlank(message: 'Vyplňte prosím e-mail')]
    #[Assert\Email(message: 'E-mail není platný')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Vyplňte prosím jméno')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Vyplňte prosím heslo')]
    // max length allowed by Symfony for security reasons
    #[Assert\Length(min: 6, max: 4096, minMessage: 'Heslo musí mít alespoň {{ limit }} znaků', maxMessage: 'Heslo je příliš dlouhé')]
    public ?string $plainPassword = null;

    public ?bool $isAdmin = null;
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
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
        // encode the plain password
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->plainPassword));
        if ($this->isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        return $user;
    }

    public static function createFromEntity(User $user, UserPasswordHasherInterface $passwordHasher): self
    {
        return new self(
            $passwordHasher,
            $user->getEmail(),
            $user->getName(),
            in_array('ROLE_ADMIN', $user->getRoles(), true)
        );
    }
}