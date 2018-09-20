<?php 

namespace Puzzle\ConnectBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Puzzle\ConnectBundle\Event\UserEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Puzzle\ConnectBundle\Util\TokenGenerator;

/**
 * @author AGNES Gnagne Cedric <cecenho5@gmail.com>
 */
class UserListener
{
	/**
	 * @var EntityManagerInterface $em
	 */
	private $em;
	
	/**
	 * @var \Swift_Mailer $mailer
	 */
	private $mailer;
	
	/**
	 * @var \Twig_Environment $twig
	 */
	private $twig;
	
	/**
	 * @var UrlGeneratorInterface
	 */
	private $router;
	
	/**
	 * @var string $fromEmail
	 */
	private $fromEmail;
	
	/**
	 * @var string $confirmationRoute
	 */
	private $confirmationRoute;
	
	/**
	 * @param EntityManagerInterface $em
	 * @param UrlGeneratorInterface $router
	 * @param \Swift_Mailer $mailer
	 * @param string $fromEmail
	 */
	public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $router, \Swift_Mailer $mailer, \Twig_Environment $twig, string $fromEmail, string $confirmationRoute){
		$this->em = $em;
		$this->mailer = $mailer;
		$this->router = $router;
		$this->twig = $twig;
		$this->fromEmail = $fromEmail;
		$this->confirmationRoute = $confirmationRoute;
	}
	
	public function onCreating(UserEvent $event) {
	    $user = $event->getUser();
	    
	    if (null === $user->getPlainPassword()) {
	        $user->setPlainPassword(TokenGenerator::generate(8));
	    }
	    
	    $user->setPassword(hash('sha512', $user->getPlainPassword()));
	    
	    if (true === $user->isEnabled()) {
	        return;
	    }
	    
	    $user->setConfirmationToken(TokenGenerator::generate(12));
	}
	
	public function onCreated(UserEvent $event) {
		$user = $event->getUser();
		
		// For test
		$template = null;
		if ($template !== null) {
		    // Notification by email
		    $this->mailer->send(array(
		        'subject' => $template->getName(),
		        'to' => $user->getEmail(),
		        'from' => $this->fromEmail,
		        'body' => $this->twig->render($template->getDocument(), [
		            'user' => $user,
		            'confirmationUrl' => $this->router->generate($this->confirmationRoute, ['token' => $user->getConfirmationToken()])
		        ])
		    ));
		}
		
		return;
	}
	
	public function onUpdatePassword(UserEvent $event) {
	    $user = $event->getUser();
	    $user->setPassword(hash('sha512', $user->getPlainPassword()));
	    
	    $this->em->flush($user);
	    return;
	}
}

?>
