<?php


namespace App\Controller;


use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordAction
{
    private ValidatorInterface $validator;
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;
    /**
     * @var JWTTokenManagerInterface
     */
    private JWTTokenManagerInterface $tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
    ) {
        $this->validator       = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager   = $entityManager;
        $this->tokenManager    = $tokenManager;
    }

    public function __invoke(User $user)
    {
        $this->validator->validate($user);

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getNewPassword()
            )
        );

        $user->setPasswordChangeDate(time());

        $this->entityManager->flush();

        $token = $this->tokenManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
