<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private Generator $faker;
    private TokenGenerator $tokenGenerator;


    private const USERS = [
        [
            'username' => 'admin',
            'password' => 'admin',
            'email'    => 'admin@email.com',
            'name'     => 'Pasha',
            'roles'    => [User::ROLE_ADMIN],
            'enabled'  => true,
        ],
        [
            'username' => 'petr',
            'password' => 'secretpetr',
            'email'    => 'petr@email.com',
            'name'     => 'Petr',
            'roles'    => [User::ROLE_EDITOR],
            'enabled'  => false,
        ],
        [
            'username' => 'vitya',
            'password' => 'secretvitya',
            'email'    => 'vitya@email.com',
            'name'     => 'Vitya',
            'roles'    => [User::ROLE_MANAGER],
            'enabled'  => true,
        ],
        [
            'username' => 'stan',
            'password' => 'secretstan',
            'email'    => 'stan@email.com',
            'name'     => 'Stan',
            'roles'    => [User::ROLE_WRITER],
            'enabled'  => true,
        ],
        [
            'username' => 'julia',
            'password' => 'secretjulia',
            'email'    => 'julia@email.com',
            'name'     => 'Julia',
            'roles'    => [User::ROLE_COMMENTER],
            'enabled'  => false,
        ],
        [
            'username' => 'katya',
            'password' => 'secretkatya',
            'email'    => 'katya@email.com',
            'name'     => 'Katya',
            'roles'    => [User::ROLE_WRITER],
            'enabled'  => true,
        ],
        [
            'username' => 'vasya',
            'password' => 'secretvasya',
            'email'    => 'vasya@email.com',
            'name'     => 'Vasya',
            'roles'    => [User::ROLE_EDITOR],
            'enabled'  => true,
        ],
        [
            'username' => 'jin',
            'password' => 'secretjin',
            'email'    => 'vasya@email.com',
            'name'     => 'Jin',
            'roles'    => [User::ROLE_COMMENTER],
            'enabled'  => true,
        ],
    ];

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        TokenGenerator $tokenGenerator
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker           = \Faker\Factory::create();
        $this->tokenGenerator  = $tokenGenerator;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $post = new BlogPost();
            $post->setAuthor($this->getUserRandomReference($post))
                 ->setTitle($this->faker->realText(20))
                 ->setPublished($this->faker->dateTime)
                 ->setContent($this->faker->realText())
                 ->setSlug($this->faker->slug);

            $this->setReference("blog_post_$i", $post);

            $manager->persist($post);
        }


        $manager->flush();
    }

    public function loadComments(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            for ($k = 0; $k < $this->faker->randomDigitNotNull; $k++) {
                $comment = new Comment();
                $comment->setAuthor($this->getUserRandomReference($comment))
                        ->setContent($this->faker->realText(50))
                        ->setPublished($this->faker->dateTimeThisYear)
                        ->setPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager): void
    {
        foreach (self::USERS as $fakeUser) {
            $user = new User();
            $user->setName($fakeUser['name'])
                 ->setEmail($fakeUser['email'])
                 ->setUsername($fakeUser['username'])
                 ->setRoles($fakeUser['roles'])
                 ->setEnabled($fakeUser['enabled']);

            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $fakeUser['password']
                )
            );

            if ( ! $user->getEnabled()) {
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomSecureToken()
                );
            }

            $this->addReference("user_{$fakeUser['username']}", $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getUserRandomReference($entity): User
    {
        $countUsers = count(self::USERS) - 1;
        $randomUser = self::USERS[random_int(0, $countUsers)];

        if ($entity instanceof BlogPost &&
            ! count(
                array_intersect(
                    $randomUser['roles'],
                    [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_WRITER]
                )
            )) {
            return $this->getUserRandomReference($entity);
        }

        if ($entity instanceof Comment &&
            ! count(
                array_intersect(
                    $randomUser['roles'],
                    [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_WRITER, User::ROLE_COMMENTER]
                )
            )) {
            return $this->getUserRandomReference($entity);
        }

        return $this->getReference('user_'.$randomUser['username']);
    }
}
