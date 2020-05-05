<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Exception\EmptyBodyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EmptyBodySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            // todo Find the Event and Priority for avoiding catching of Confirm while this is developed for Create. Maybe entity validator will be needed.
            KernelEvents::REQUEST => ['handleEmptyBody', EventPriorities::POST_DESERIALIZE],
        ];
    }

    public function handleEmptyBody(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $method  = $request->getMethod();
        $route   = $request->get('_route');

        if ( ! in_array($method, [Request::METHOD_POST, Request::METHOD_PUT], true) ||
             in_array($request->getContentType(), ['html', 'form']) ||
             strpos($route, 'api') !== 0
        ) {
            return;
        }

        $newUser = $event->getRequest()->get('data');

        if ($newUser === null) {
            throw new EmptyBodyException();
        }
    }
}
