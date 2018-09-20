<?php

namespace Puzzle\ConnectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="token")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Puzzle\ConnectBundle\Repository\TokenRepository")
 */
class Token
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Puzzle\ConnectBundle\Service\IdGenerator")
     */
    protected $id;
    
	/**
	 * @ORM\Column(name="access_token", type="string")
	 */
	protected $accessToken;
	
	/**
	 * @ORM\Column(name="refresh_token", type="string")
	 */
	protected $refreshToken;
	
	/**
	 * @ORM\Column(name="expires_at", type="datetime")
	 */
	protected $expiresAt;
	
	/**
     * @ORM\OneToOne(targetEntity="User", inversedBy="token")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
	
	public function getId() : string{
	    return $this->id;
	}
	
	public function setAccessToken($accessToken) : self{
	    $this->accessToken = $accessToken;
	    return $this;
	}
	
	public function getAccessToken() : string {
	    return $this->accessToken;
	}
	
	public function setRefreshToken($refreshToken) : self {
	    $this->refreshToken = $refreshToken;
	    return $this;
	}
	
	public function getRefreshToken() : string {
	    return $this->refreshToken;
	}
	
	public function setExpiresAt($expiresAt) : self{
	    $this->expiresAt = $expiresAt;
	    return $this;
	}
	
	public function getExpiresAt() {
	    return $this->expiresAt;
	}
	
	public function setUser(User $user) :self {
	    $this->user = $user;
	    return $this;
	}
	
	public function getUser() :User {
	    return $this->user;
	}
}
