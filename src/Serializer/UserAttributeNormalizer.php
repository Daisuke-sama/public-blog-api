<?php


namespace App\Serializer;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class UserAttributeNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const USER_ATTR_NORMALIZER_ALREADY_CALLED = 'USER_ATTR_NORMALIZER_ALREADY_CALLED';

    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * UserAttributeSerializer constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        if (isset($context[self::USER_ATTR_NORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($this->isUserHimself($object)) {
            $context['groups'][] = 'user:owner:get';
        }

        return $this->passOn($object, $format, $context); // continues the serialization
    }

    private function isUserHimself(User $object)
    {
        return $object->getUsername() === $this->tokenStorage->getToken()->getUsername();
    }

    private function passOn($object, ?string $format, array $context)
    {
        if ( ! $this->serializer instanceof NormalizerInterface) {
            throw new \LogicException(
                sprintf(
                    'Cannot normalize object "%s" because the injected serializer is not a normalizer',
                    $object
                )
            );
        }

        $context[self::USER_ATTR_NORMALIZER_ALREADY_CALLED] = true;

        return $this->serializer->normalize($object, $format, $context);
    }
}
