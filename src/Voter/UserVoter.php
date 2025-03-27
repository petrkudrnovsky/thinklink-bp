<?php

namespace App\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

# Source: https://symfony.com/doc/current/security/voters.html#creating-the-custom-voter
class UserVoter extends Voter
{
    const VIEW = 'view';
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const INDEX = 'index';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::INDEX])){
            return false;
        }

        if(!$subject instanceof User){
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if(!$user instanceof User){
            return false;
        }

        // this is possible thanks to supports() method
        $file = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($file, $user),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($file, $user),
            self::DELETE => $this->canDelete($file, $user),
            self::INDEX => $this->canViewIndex($user),
            default => false,
        };
    }

    private function isAdmin(User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canView(User $accessedUser, User $accessingUser): bool
    {
        return $this->isAdmin($accessingUser) || $accessedUser === $accessingUser;
    }

    private function canCreate(User $accessingUser): bool
    {
        return $this->isAdmin($accessingUser);
    }

    private function canEdit(User $accessedUser, User $accessingUser): bool
    {
        return $this->isAdmin($accessingUser) || $accessedUser === $accessingUser;
    }

    private function canDelete(User $accessedUser, User $accessingUser): bool
    {
        return $this->isAdmin($accessingUser);
    }

    private function canViewIndex(User $accessingUser): bool
    {
        return $this->isAdmin($accessingUser);
    }
}