<?php
namespace Acme\ChatBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AcmeChatExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event){

        // Get the exception object from the received event
        $exception = $event->getException();

        $exception_content = array(
            'errors' => array(
                'message' => $exception->getMessage(),
            ),
        );


        // Customize our response object to display the exception details
        $response = new JsonResponse();
        $response->setData($exception_content);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}