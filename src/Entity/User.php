<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\ResetPasswordAction;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups" = {"user:get"}
 *     },
 *     itemOperations = {
 *          "get" = {
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "normalization_context"={
 *                  "groups"={"user:get"}
 *              }
 *          },
 *          "put" = {
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "denormalization_context"={
 *                  "groups"={"user:put"}
 *              },
 *              "normalization_context"={
 *                  "groups"={"user:get"}
 *              }
 *          },
 *          "user:put:reset-pass" = {
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "method" = "PUT",
 *              "path" = "/users/{id}/reset-password",
 *              "controller" = ResetPasswordAction::class,
 *              "denormalization_context"={
 *                  "groups"={"user:put:reset-pass"}
 *              }
 *          }
 *     },
 *     collectionOperations = {
 *          "get" = {
 *              "normalization_context" = {
 *                  "groups" = {"user:get"}
 *              }
 *          },
 *          "post" = {
 *              "denormalization_context" = {
 *                  "groups" = {"user:post"}
 *              },
 *              "normalization_context" = {
 *                  "groups" = {"user:get"}
 *              }
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"})
 * @UniqueEntity(fields={"email"})
 */
class User implements UserInterface
{
    public const ROLE_COMMENTER = 'ROLE_COMMENTER';
    public const ROLE_WRITER = 'ROLE_WRITER';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_MANAGER = 'ROLE_MANAGER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTER];

    /**
     * @Groups({"user:get"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Groups({"user:get", "user:post", "comment:get"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Length(min=3, max=255, groups={"user:post"})
     */
    private ?string $username = null;

    /**
     * @Groups({"user:post"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Regex(
     *     groups={"user:post"},
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{3,}/",
     *     message="Password must be three characters long and contain at least one digit, one uppercase letter and one lower case letter."
     * )
     */
    private ?string $password = null;

    /**
     * @Groups({"user:post"})
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Passwords does not match",
     *     groups={"user:post"}
     * )
     */
    private ?string $retypedPassword = null;

    /**
     * @Groups({"user:put:reset-pass"})
     * @Assert\NotBlank(groups={"user:put:reset-pass"})
     * @Assert\Regex(
     *     groups={"user:put:reset-pass"},
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{3,}/",
     *     message="Password must be three characters long and contain at least one digit, one uppercase letter and one lower case letter."
     * )
     */
    private ?string $newPassword = null;

    /**
     * @Groups({"user:put:reset-pass"})
     * @Assert\NotBlank(groups={"user:put:reset-pass"})
     * @Assert\Expression(
     *     "this.getNewPassword() === this.getRetypedNewPassword()",
     *     message="Passwords does not match",
     *     groups={"user:put:reset-pass"}
     * )
     */
    private ?string $retypedNewPassword = null;

    /**
     * @Groups({"user:put:reset-pass"})
     * @Assert\NotBlank(groups={"user:put:reset-pass"})
     * @UserPassword(groups={"user:put:reset-pass"})
     */
    private ?string $oldPassword = null;

    /**
     * @Groups({"user:get", "user:post", "user:put", "comment:get", "blog:get"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Length(min=3, max=50, groups={"user:post", "user:put"})
     */
    private ?string $name = null;

    /**
     * @Groups({"user:post", "user:put", "user:manager:get", "user:owner:get"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Email(groups={"user:post", "user:put"})
     * @Assert\Length(min=6, max=255, groups={"user:post", "user:put"})
     */
    private ?string $email = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"user:get"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"user:get"})
     */
    private $comments;

    /**
     * @Groups({"user:manager:get", "user:owner:get"})
     * @ORM\Column(type="simple_array", length=300)
     */
    private array $roles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $passwordChangeDate = null;

    /**
     * @Groups({"user:get"})
     * @ORM\Column(type="boolean")
     */
    private bool $enabled = false;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private ?string $confirmationToken = null;


    public function __construct()
    {
        $this->posts    = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles    = self::DEFAULT_ROLES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {

    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword(string $retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getRetypedNewPassword(): ?string
    {
        return $this->retypedNewPassword;
    }

    public function setRetypedNewPassword(?string $retypedNewPassword): self
    {
        $this->retypedNewPassword = $retypedNewPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getPasswordChangeDate(): ?int
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate(int $passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }
}
