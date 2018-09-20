<?php
namespace Puzzle\ConnectBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Service\ErrorFactory;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;

class ApiResponseListener implements EventSubscriberInterface
{
	/**
	 * @var Session $session
	 */
    private $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents() {
		return [
				ApiEvents::API_BAD_RESPONSE => 'onBadResponse',
		];
	}
    
	public function onBadResponse(ApiResponseEvent $event) {
	    $exception = $event->getException();
	    $request = $event->getRequest();
	    $error = ErrorFactory::createDefaultError($exception);
	    
	    if ($request->isXmlHttpRequest() === true) {
	        $event->setResponse(new JsonResponse($error['message'], $error['code']));
	    }else {
	        $this->session->getFlashBag()->add('error', $error['message']);
	    }
	}
	
}
