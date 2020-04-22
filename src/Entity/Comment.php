<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\CouplingInterfaces\AuthoredEntityInterface;
use App\Entity\CouplingInterfaces\PublishedDateEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *          "order"={"published": "DESC"},
 *          "pagination_enabled"=false,
 *          "pagination_client_enabled"=true,
 *          "pagination_client_items_per_page"=true
 *     },
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "denormalization_context"={
 *                  "groups"={"comment:put"}
 *              },
 *              "access_control"="is_granted('ROLE_EDITOR') or is_granted('ROLE_COMMENTER') && object.getAuthor() == user"
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('ROLE_COMMENTER')"
 *          },
 *          "api_blog_posts_comments_get_subresource"={
 *              "normalization_context"={
 *                  "groups"={"comment:get:with-author"}
 *              }
 *          }
 *     },
 *     normalizationContext={
 *          "groups"={"comment:get"}
 *     },
 *     denormalizationContext={
 *          "groups"={"comment:post"}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @Groups({"comment:get:with-author"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Groups({"comment:get", "comment:post", "comment:put", "comment:get:with-author"})
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=10)
     */
    private ?string $content = null;

    /**
     * @Groups({"comment:get", "comment:get:with-author"})
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $published = null;

    /**
     * @Groups({"comment:get", "comment:get:with-author"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $author = null;

    /**
     * @Groups({"comment:post"})
     * @ORM\ManyToOne(targetEntity="App\Entity\BlogPost", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?BlogPost $post = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): AuthoredEntityInterface
    {
        $this->author = $author;

        return $this;
    }

    public function getPost(): ?BlogPost
    {
        return $this->post;
    }

    public function setPost(BlogPost $post): self
    {
        $this->post = $post;

        return $this;
    }
}
