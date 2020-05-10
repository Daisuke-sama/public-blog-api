<?php


namespace App\Tests\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\EventSubscriber\AuthoredEntitySubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthoredEntitySubscriberTest extends TestCase
{
    public function testConfiguration(): void
    {
        $result = AuthoredEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $result);
        $this->assertEquals(
            ['getAuthenticatedUser', EventPriorities::PRE_WRITE],
            $result[KernelEvents::VIEW]
        );
    }

    /**
     * @dataProvider setAuthorCallProvider
     */
    public function testSetAuthorCall(string $className, bool $isCallSetAuthor, string $httpMethod): void
    {
        $entityMock       = $this->getEntityMock($className, $isCallSetAuthor);
        $tokenStorageMock = $this->getTokenStorageMock(true);
        $eventMock        = $this->getEventMock($httpMethod, $entityMock);

        (new AuthoredEntitySubscriber($tokenStorageMock))->getAuthenticatedUser(
            $eventMock
        );
    }

    public function testNoTokenThere(): void
    {
        $tokenStorageMock = $this->getTokenStorageMock(false);
        $eventMock        = $this->getEventMock(
            'POST',
            new class {
            }
        );

        (new AuthoredEntitySubscriber($tokenStorageMock))->getAuthenticatedUser(
            $eventMock
        );
    }

    public function setAuthorCallProvider(): array
    {
        return [
            [BlogPost::class, true, 'POST'],
            ['Strange', false, 'POST'],
            [BlogPost::class, false, 'GET'],
            [Comment::class, true, 'POST'],
        ];
    }

    /**
     * @param bool $hasToken
     *
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock(bool $hasToken): MockObject
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
                          ->getMockForAbstractClass();

        $tokenMock->expects($hasToken ? $this->once() : $this->never())
                  ->method('getUser')
                  ->willReturn(new User());

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
                                 ->getMockForAbstractClass();

        $tokenStorageMock->expects($this->once())
                         ->method('getToken')
                         ->willReturn($hasToken ? $tokenMock : null);

        return $tokenStorageMock;
    }

    /**
     * @param string $method
     * @param $controllerResult
     *
     * @return MockObject|ViewEvent
     */
    private function getEventMock(string $method, $controllerResult): MockObject
    {
        $requestMock = $this->getMockBuilder(Request::class)
                            ->getMock();

        $requestMock->expects($this->once())
                    ->method('getMethod')
                    ->willReturn($method);

        $eventMock = $this->getMockBuilder(ViewEvent::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $eventMock->expects($this->once())
                  ->method('getControllerResult')
                  ->willReturn($controllerResult);

        $eventMock->expects($this->once())
                  ->method('getRequest')
                  ->willReturn($requestMock);

        return $eventMock;
    }

    private function getEntityMock(string $className, bool $isCallSetAuthor): MockObject
    {
        $entityMock = $this->getMockBuilder($className)
                           ->setMethods(['setAuthor'])
                           ->getMock();
        $entityMock->expects($isCallSetAuthor ? $this->once() : $this->never())
                   ->method('setAuthor');

        return $entityMock;
    }
}
