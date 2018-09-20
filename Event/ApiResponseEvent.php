<?php
namespace Puzzle\ConnectBundle\Event;

use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class ApiResponseEvent extends GetResponseEvent
{
	/**
	 * @var Request $request
	 */
    protected $request;
	
	/**
	 * @var BadResponseException
	 */
	protected $exception;

	
	public function __construct(BadResponseException $exception, Request $request) {
		$this->exception = $exception;
		$this->request = $request;
	}

	public function getException() : BadResponseException {
	    return $this->exception;
	}
	
	public function getRequest() :? Request {
	    return $this->request;
	}
}
