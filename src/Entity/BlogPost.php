<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\CouplingInterfaces\AuthoredEntityInterface;
use App\Entity\CouplingInterfaces\PublishedDateEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *          "title": "partial",
 *          "content": "ipartial",
 *          "author": "exact",
 *          "author.name": "ipartial"
 *     }
 * )
 * @ApiFilter(
 *     DateFilter::class,
 *     properties={
 *          "published"
 *     }
 * )
 * @ApiFilter(
 *     RangeFilter::class,
 *     properties={
 *          "id"
 *     }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id",
 *          "published",
 *          "title"
 *     },
 *     arguments={"orderParameterName"="_order"}
 * )
 * @ApiFilter(
 *     PropertyFilter::class,
 *     arguments={
 *          "parameterName": "properties",
 *          "overrideDefaultProperties": false,
 *          "whitelist": {"id", "author", "slug", "title", "content"}
 *     }
 * )
 * @ApiResource(
 *     attributes={"order"={"published": "DESC"}, "maximum_items_per_page"=15},
 *     itemOperations={
 *          "get",
 *          "put" = {
 *              "denormalization_context"={
 *                  "groups"={"blog:put"}
 *              },
 *              "access_control"="is_granted('ROLE_EDITOR') or is_granted('ROLE_WRITER') and object.getAuthor() == user"
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post" = {
 *              "access_control"="is_granted('ROLE_WRITER')"
 *          }
 *     },
 *     normalizationContext={
 *          "groups"={"blog:get"}
 *     },
 *     denormalizationContext={
 *          "groups"={"blog:post"}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BlogPostRepository")
 */
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Groups({"blog:get", "blog:post"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private ?string $title = null;

    /**
     * @Groups({"blog:get", "blog:post"})
     * @ORM\Column(type="datetime")
     * @Assert\Type(type="\DateTime")
     */
    private ?\DateTimeInterface $published = null;

    /**
     * @Groups({"blog:get", "blog:post", "blog:put"})
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=20)
     */
    private ?string $content = null;

    /**
     * @Groups({"blog:get"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $author = null;

    /**
     * @Groups({"blog:get", "blog:post", "blog:put"})
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private ?string $slug = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     * @ORM\JoinColumn(nullable=true)
     * @ApiSubresource()
     */
    private $comments;

    /**
     * @Groups({"blog:get", "blog:post", "blog:put"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Image")
     * @ORM\JoinTable()
     * @ApiSubresource()
     */
    private $images;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images   = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

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

    public function getComments()
    {
        return $this->comments;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        $this->images->add($image);

        return $this;
    }

    public function removeImage(Image $image): void
    {
        $this->images->removeElement($image);
    }

    public function __toString(): string
    {
        return $this->title ?? 'untitled';
    }
}
