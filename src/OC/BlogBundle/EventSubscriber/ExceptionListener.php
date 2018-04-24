<?php

namespace OC\BlogBundle\EventSubscriber;


use JMS\Serializer\Serializer;
use OC\BlogBundle\Normalizer\NormalizerChain;
use OC\BlogBundle\Normalizer\NormalizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    private $serializer;
    private $normalizerChain;

    public function __construct(Serializer $serializer, NormalizerChain $normalizerChain)
    {
        $this->serializer = $serializer;
        $this->normalizerChain = $normalizerChain;
    }

    public function processException(GetResponseForExceptionEvent $event)
    {
        $result = null;

        foreach($this->normalizerChain->getNormalizers() as $normalizer) {
            if($normalizer->supports($event->getException())) {
                $result = $normalizer->normalize($event->getException());

                break;
            }
        }

        if(null == $result) {
            $result['code'] = Response::HTTP_BAD_REQUEST;

            $result['body'] = array(
                'code'      => Response::HTTP_BAD_REQUEST,
                'message'   => $event->getException()->getMessage()
            );
        }

        $body = $this->serializer->serialize($result['body'], 'json');

        $event->setResponse(new Response($body, $result['code']));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(
                array('processException', 255)
            ),
        );
    }

}