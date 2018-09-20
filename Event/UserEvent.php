<?php 

namespace Puzzle\ConnectBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Puzzle\ConnectBundle\Entity\User;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class UserEvent extends Event
{
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * @var array
	 */
	private $data;
	
	/**
	 * @param User $user
	 * @param array $data
	 */
	public function __construct(User $user, array $data = null) {
		$this->user= $user;
		$this->data = $data;
	}
	
	public function getUser(){
		return $this->user;
	}
	
	public function getData(){
		return $this->data;
	}
}

?>