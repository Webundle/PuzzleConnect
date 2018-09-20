<?php

namespace Puzzle\ConnectBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Puzzle\ConnectBundle\Entity\User;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
	/**
	 * @var EntityRepository
	 */
    protected $userRepository;
	
	/**
	 * @param EntityManagerInterface $em
	 */
	public function __construct(EntityManagerInterface $em)
	{     
	    $this->em = $em;
	    $this->userRepository = $this->em->getRepository(User::class);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
	 */
	public function loadUserByUsername($username)
	{
		$user = $this->userRepository
        		  ->createQueryBuilder('u')
        		  ->where('u.username = :username OR u.email = :email')
        		  ->setParameter('username', $username)
        		  ->setParameter('email', $username)
        		  ->getQuery()
        		  ->getSingleResult();
	   
		if (!$user instanceof AdvancedUserInterface) {
		    $message = sprintf(
		        'Unable to find an active admin PuzzleConnectBundle:User object identified by "%s".',
		        $username
		    );
		    $ex = new UsernameNotFoundException($message);
		    $ex->setUser($user);
		    throw $ex;
		}
		
// 		if ($user->hasRole('ROLE_USER')) {
// 		    $ex = new AccessDeniedException('Your account is not allowed in this space.');
// 		    throw $ex;
// 		}
		
		if (!$user->isAccountNonLocked()) {
		    $ex = new LockedException('User account is locked.');
		    $ex->setUser($user);
		    throw $ex;
		}
		
		if (!$user->isEnabled()) {
		    $ex = new DisabledException('User account is disabled.');
		    $ex->setUser($user);
		    throw $ex;
		}
		
		if (!$user->isAccountNonExpired()) {
		    $ex = new AccountExpiredException('User account has expired.');
		    $ex->setUser($user);
		    throw $ex;
		}
		
		if (!$user->isCredentialsNonExpired()) {
		    $ex = new CredentialsExpiredException('User credentials has expired.');
		    $ex->setUser($user);
		    throw $ex;
		}
	
		return $user;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
	 */
	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		if (!$this->supportsClass($class)) {
			throw new UnsupportedUserException(
				sprintf(
					'Instances of "%s" are not supported.',
					$class
				)
			);
		}

		return $this->userRepository->find($user->getId());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
	 */
	public function supportsClass($class)
	{
		return $this->userRepository->getClassName() === $class
		|| is_subclass_of($class, $this->userRepository->getClassName());
	}
	
}