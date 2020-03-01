<?php
declare(strict_types=1);


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default_route")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(
            [
                'action' => 'index',
                'time'   => time(),
            ]
        );
    }
}