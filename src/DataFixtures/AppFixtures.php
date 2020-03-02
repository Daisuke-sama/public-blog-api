<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $post = new BlogPost();
        $post->setTitle('Great fixture')
             ->setPublished(new \DateTime('2019-12-15 03:03:03'))
             ->setAuthor('Dane')
             ->setContent('the content full')
             ->setSlug('great-fixture');

        $manager->persist($post);

        $post = new BlogPost();
        $post->setTitle('Greatest fixture')
             ->setPublished(new \DateTime('2019-10-15 12:12:12'))
             ->setAuthor('Diana')
             ->setContent('the content full')
             ->setSlug('greatest-fixture');

        $manager->persist($post);

        $manager->flush();
    }
}
