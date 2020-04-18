<?php
declare(strict_types=1);


namespace App\Controller;


use App\Security\UserConfirmationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default_route")
     */
    public function index(): Response
    {
        return $this->render(
            'base.html.twig'
        );
    }

    /**
     * @Route("/confirm-user/{token}", name="default_user_confirmation")
     * @param string $token
     * @param UserConfirmationService $userConfirmationService
     *
     * @return Response
     */
    public function confirmUser(
        string $token,
        UserConfirmationService $userConfirmationService): Response
    {
        $userConfirmationService->confirmUser($token);

        return $this->redirectToRoute('default_route');
    }
}
