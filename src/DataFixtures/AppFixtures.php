<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;
    /**
     * @var \Faker\Generator
     */
    private \Faker\Generator $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker           = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $this->getReference('admin');

        for ($i = 0; $i < 50; $i++) {
            $post = new BlogPost();
            $post->setAuthor($user)
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
                $comment->setAuthor($this->getReference('admin'))
                        ->setPublished($this->faker->dateTimeThisYear)
                        ->setContent($this->faker->realText(50))
                        ->setPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Tolik')
             ->setEmail('abc@api.loc')
             ->setUsername('tolik');

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                'password'
            )
        );

        $this->addReference('admin', $user);

        $manager->persist($user);
        $manager->flush();
    }
}
