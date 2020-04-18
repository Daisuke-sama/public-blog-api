<?php


namespace App\Security;


use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEnabledChecker implements UserCheckerInterface
{
    /**
     * @inheritDoc
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if ( ! $user instanceof User) {
            return;
        }

        if ( ! $user->getEnabled()) {
            throw new DisabledException();
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // TODO: Implement checkPostAuth() method.
    }
}
