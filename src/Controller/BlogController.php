<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    private const POSTS = [
        ['id' => 1, 'name' => 'The First', 'slug' => 'the-first'],
        ['id' => 2, 'name' => 'The Second', 'slug' => 'the-second'],
        ['id' => 3, 'name' => 'The Third', 'slug' => 'the-third'],
    ];

    /**
     * @Route("/{page}", name="blog.list", defaults={"page": 3})
     */
    public function list(int $page = 1, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);

        return $this->json(
            [
                'page'  => $page,
                'limit' => $limit,
                'data'  => array_map(
                    fn($item) => $this->generateUrl('blog.by.slug', ['slug' => $item['slug']]),
                    self::POSTS
                ),
            ]
        );
    }

    /**
     * @Route("/post/{id}", name="blog.id", requirements={"id"="\d+"})
     */
    public function post(int $id): JsonResponse
    {
        return $this->json(
            self::POSTS[array_search($id, array_column(self::POSTS, 'id'), true)]
        );
    }

    /**
     * @Route("/post/{slug}", name="blog.by.slug")
     */
    public function postBySlug(string $slug): JsonResponse
    {
        return $this->json(
            self::POSTS[array_search($slug, array_column(self::POSTS, 'slug'), true)]
        );
    }
}
