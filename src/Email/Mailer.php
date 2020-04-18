<?php


namespace App\Email;


use App\Entity\User;
use Twig\Environment;

class Mailer
{
    private \Swift_Mailer $mailer;
    private Environment $twig;

    public function __construct(\Swift_Mailer $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendConfirmationEmail(User $user): void
    {
        $body = $this->twig->render(
            'email/confirmation.html.twig',
            [
                'user' => $user,
            ]
        );

        $msg = (new \Swift_Message('You have been registered!'))
            ->setFrom('admin@api.loc')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
        ;

        $this->mailer->send($msg);
    }
}
