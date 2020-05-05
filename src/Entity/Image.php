<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Controller\UploadImageAction;

/**
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ApiResource(
 *     attributes={"order"={"id": "DESC"}},
 *     collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={"image:get"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/images",
 *              "controller"=UploadImageAction::class,
 *              "defaults"={"_api_receive"=false},
 *              "normalization_context"={
 *                  "groups"={"image:post"}
 *              }
 *          }
 *     }
 * )
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="url")
     * @Assert\NotNull()
     */
    private $file;

    /**
     * @Groups({"image:get", "image:post", "blog:get"})
     * @ORM\Column(nullable=true)
     */
    private ?string $url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getUrl(): ?string
    {
        return '/images/' . $this->url;
    }

    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    public function __toString(): string
    {
        return $this->url ?? '';
    }
}
