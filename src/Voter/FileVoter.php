<?php

namespace App\Voter;

use App\Entity\FilesystemFile;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

# Source: https://symfony.com/doc/current/security/voters.html#creating-the-custom-voter
class FileVoter extends Voter
{
    const VIEW = 'view';
    const DELETE = 'delete';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, [self::VIEW, self::DELETE])){
            return false;
        }

        if(!$subject instanceof FilesystemFile){
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
            self::DELETE => $this->canDelete($file, $user),
            default => false,
        };
    }

    private function canView(FilesystemFile $file, User $user): bool
    {
        return $user === $file->getOwner();
    }

    private function canDelete(FilesystemFile $file, User $user): bool
    {
        return $user === $file->getOwner();
    }
}