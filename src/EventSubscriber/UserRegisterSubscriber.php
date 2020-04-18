<?php
declare(strict_types=1);

namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Email\Mailer;
use App\Entity\User;
use App\Security\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{
    private UserPasswordEncoderInterface $passEncoder;
    private TokenGenerator $tokenGenerator;
    private Mailer $mailer;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        TokenGenerator $tokenGenerator,
        Mailer $mailer
    ) {
        $this->passEncoder    = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer         = $mailer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE],
        ];
    }

    public function userRegistered(ViewEvent $event): void
    {
        $user   = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ( ! $user instanceof User || $method !== Request::METHOD_POST) {
            return;
        }

        $user->setPassword(
            $this->passEncoder->encodePassword($user, $user->getPassword())
        );

        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );

        $this->mailer->sendConfirmationEmail($user);
    }
}
