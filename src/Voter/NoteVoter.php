<?php

namespace App\Voter;

use App\Entity\Note;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

# Source: https://symfony.com/doc/current/security/voters.html#creating-the-custom-voter
class NoteVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])){
            return false;
        }

        if(!$subject instanceof Note){
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
        $note = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($note, $user),
            self::EDIT => $this->canEdit($note, $user),
            self::DELETE => $this->canDelete($note, $user),
            default => false,
        };
    }

    private function canView(Note $note, User $user): bool
    {
        return $user === $note->getOwner();
    }

    private function canEdit(Note $note, User $user): bool
    {
        return $user === $note->getOwner();
    }

    private function canDelete(Note $note, User $user): bool
    {
        return $user === $note->getOwner();
    }
}