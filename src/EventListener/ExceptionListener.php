<?php


namespace App\EventListener;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as LegacyExceptionListener;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListener
{
    private $exceptionListener;

    public function __construct($controller, LoggerInterface $logger = null, $debug = false, ErrorListener $errorListener = null)
    {
        $this->exceptionListener = $errorListener ? new ErrorListener($controller, $logger, $debug) : new LegacyExceptionListener($controller, $logger, $debug);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by API Platform
        if (
            'html' === $request->getRequestFormat('') ||
            ! ((RequestAttributesExtractor::extractAttributes($request)['respond'] ?? $request->attributes->getBoolean('_api_respond', false)) || $request->attributes->getBoolean('_graphql', false))
        ) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof InvalidArgumentException &&
            $exception->getPrevious() instanceof ItemNotFoundException
        ) {
            $violations = new ConstraintViolationList(
                [
                    new ConstraintViolation(
                        $exception->getMessage(),
                        null,
                        [],
                        '',
                        '',
                        ''
                    ),
                ]
            );

            $excep = new ValidationException($violations);
            $event->setThrowable($excep);

            return;
        }
    }
}
