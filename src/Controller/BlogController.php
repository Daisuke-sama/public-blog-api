<?php


namespace App\Controller;


use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/{page}", name="blog.list", defaults={"page": 3}, requirements={"page"="\d+"})
     */
    public function list(Request $request, int $page = 1): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $repo  = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repo->findAll();

        return $this->json(
            [
                'page'  => $page,
                'limit' => $limit,
                'data'  => array_map(
                    fn(BlogPost $item) => $this->generateUrl('blog.by.slug', ['slug' => $item->getSlug()]),
                    $items
                ),
            ]
        );
    }

    /**
     * @Route("/post/{id}", name="blog.id", requirements={"id"="\d+"}, methods={"GET"})
     * @ParamConverter("post", class="App\Entity\BlogPost")
     */
    public function post($post): JsonResponse
    {
        return $this->json($post);
    }

    /**
     * @Route("/post/{slg}", name="blog.by.slug", methods={"GET"})
     * @ParamConverter("post", class="App\Entity\BlogPost", options={"mapping": {"slg": "slug"}})
     */
    public function postBySlug($post): JsonResponse
    {
        return $this->json($post);
    }

    /**
     * @Route("/add", name="blog.create", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }

    /**
     * @Route("/post/{id}", name="blog.delete", methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
