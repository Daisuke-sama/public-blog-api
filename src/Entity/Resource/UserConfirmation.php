<?php


namespace App\Entity\Resource;


use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "post"={
 *              "path"="/users/confirm"
 *          }
 *     }
 * )
 */
class UserConfirmation
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=5,max=30)
     */
    public ?string $confirmationToken;
}
