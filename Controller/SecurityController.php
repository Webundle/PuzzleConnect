<?php
namespace Puzzle\ConnectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;
use Puzzle\ConnectBundle\Entity\User;
use Puzzle\ConnectBundle\Event\UserEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Client as ClientHttp;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Puzzle\ConnectBundle\Entity\Token;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Puzzle\ConnectBundle\Util\TokenGenerator;
use Doctrine\ORM\EntityManager;
use Puzzle\ConnectBundle\UserEvents;

/**
 * Security 
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class SecurityController extends Controller
{
    use TargetPathTrait;
    
	/**
	 * Main Login
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginAction(Request $request){
	    
	    /** @var Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils */
	    $authenticationUtils = $this->get('security.authentication_utils');
	    
	    $error = $authenticationUtils->getLastAuthenticationError();
	    $lastUsername = $authenticationUtils->getLastUsername();
	    
	    $csrfToken = $this->has('form.csrf_provider')
	    ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
	    : null;
	    
	    if ($request->getHost() === $this->getParameter('host_admin')){
	        return $this->redirectToRoute('oauth_login', $request->query->all());
	    }
	    
	    return $this->render($this->getParameter('puzzle_app.template_bundle')."/Security/login.html.twig", array(
	        'last_username'  => $lastUsername,
	        'error'          => $error,
	        'csrf_token'     => $csrfToken
	    ));
	}
	
	
	/**
	 * OAuth Register
	 *
	 * @param Request $request
	 * @return Response | RedirectResponse
	 */
	public function registerAction(Request $request) {
	    if ($request->isMethod('POST') == true) {
	        try {
	            $data = $request->request->all();
	            /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            /** @var \Doctrine\ORM\EntityManager $em */
	            $em = $this->get('doctrine.orm.default_entity_manager');
	            
	            $user = new User();
	            $user->setFirstName($data['firstName']);
	            $user->setLastName($data['lastName']);
	            $user->setEmail($data['email']);
	            $user->setUsername($data['username']);
	            $user->setPlainPassword($data['password']);
	            $user->setEnabled(false);
	            
	            $event = new UserEvent($user);
	            $dispatcher->dispatch(UserEvents::USER_CREATING, $event);
	            $plainPassword = $user->getPlainPassword();
	            
	            $em->persist($user);
	            $em->flush();
	            
	            $event = new UserEvent($user, ['plainPassword' => $plainPassword]);
	            $dispatcher->dispatch(UserEvents::USER_CREATED, $event);
	            
	            $user->setRoles([User::ROLE_DEFAULT]);
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($user);
	            $em->flush();
	            /** @var EventDispatcher */
	            $this->get('event_dispatcher')->dispatch(UserEvents::USER_PASSWORD, new UserEvent($user, [
	                'plainPassword' => $user->getPlainPassword()
	            ]));
	            
	            $roles = $user->getRoles() ?? [User::ROLE_DEFAULT];
	            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $roles);
	            /** @var GuardAuthenticatorHandler $authenticationGuardHandler */
	            $authenticationGuardHandler = $this->get('security.authentication.guard_handler');
	            $authenticationGuardHandler->authenticateWithToken($token, $request);
	            return $this->redirect($this->getTargetPath($request->getSession(), 'main'));
	            
	        } catch (UniqueConstraintViolationException $e) {
	            $this->addFlash('error', 'message.duplicated_account');
	            return $this->redirectToRoute('register');
	        }
	    }
	    
	    return $this->render($this->getParameter('puzzle_app.template_bundle')."/Security/register.html.twig");
	}
	
	/**
	 * OAuth Login URL Generator
	 * Redirect to pos_oauth_login
	 *
	 * @param Request $request
	 * @return Response | RedirectResponse
	 */
	public function oauthLoginUrlGeneratorAction(Request $request){
	    $config = $this->getParameter('puzzle_connect');
	    $redirectUri = $request->query->get('redirect_uri') ?? $this->generateUrl($config['default_redirect_uri'], [], UrlGeneratorInterface::ABSOLUTE_URL);
	    $scope = $request->query->get('scope') ?? $config['default_scope'];
	    /** @var Puzzle\ConnectBundle\Service\POSGrantUrlGenerator $posUrlGenerator */
	    $posUrlGenerator = $this->get('puzzle_connect.pos.url_generator');
	    
	    return $this->redirect($posUrlGenerator->generateLoginUrl($scope, $redirectUri));
	}
	
	/**
	 * OAuth Connect Client
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function oauthConnectAction(Request $request)
	{
	    /** @var Puzzle\ConnectBundle\Service\POSGrantUrlGenerator $posUrlGenerator */
	    $posUrlGenerator = $this->get('puzzle_connect.pos.url_generator');
	    if ($code = $request->query->get('code')){
	        $redirectUri = strtok($request->getUri(), '?');
	        $url = $posUrlGenerator->generateAuthorizationCodeUrl($code, $redirectUri);
	        
	        $clientHttp = new ClientHttp();
	        $response = $clientHttp->get($url, [
	            'Accept'       => 'application/json',
	            'Content-type’ => ‘application/json'
	        ]);
	        $array = json_decode($response->getBody()->getContents(), true);
	        
	        if (array_key_exists('access_token', $array)){
	            $url = $this->getParameter('puzzle_connect')['base_apis_uri'].'/v1/users/me.json';
	            $response = $clientHttp->get($url, [
	                'headers'      => [
	                    'Accept'           => 'application/json',
	                    'Content-Type'     => 'application/json',
	                    'Authorization'    => 'Bearer '.$array['access_token']
	                ]
	            ]);
	            $apiUser = json_decode($response->getBody()->getContents(), true);
	            /** @var EntityManager $em */
	            $em = $this->getDoctrine()->getManager();
	            
	            if (!$user = $em->getRepository(User::class)->findOneBy(array('email' => $apiUser['email']))){
	                $user = new User();
	                $user->setFirstName($apiUser['first_name']);
	                $user->setLastName($apiUser['last_name'] ?? "");
	                $user->setEmail($apiUser['email']);
	                $user->setUsername($apiUser['email']);
	                $user->setPlainPassword(TokenGenerator::generate(8));
	                $user->setRoles([User::ROLE_ADMIN]);
	                
	                $em->persist($user);
	                $em->flush();
	                
	                /** @var EventDispatcher */
	                $this->get('event_dispatcher')->dispatch(UserEvents::USER_PASSWORD, new UserEvent($user, [
	                    'plainPassword' => $user->getPlainPassword()
	                ]));
	            }
	            
	            $token = $em->getRepository(Token::class)->findOneBy(['user' => $user->getId()]);
	            if (!$token){
	                $token = new Token();
	                $token->setUser($user);
	                $em->persist($token);
	            }
	            
	            $token->setAccessToken($array['access_token']);
	            $token->setRefreshToken($array['refresh_token']);
	            
	            $date = new \DateTime();
	            $date->setTimestamp(time() + $array['expires_in']);
	            $token->setExpiresAt($date);
	            
	            $em->flush();
	            
	            $roles = $user->getRoles() ?? [User::ROLE_DEFAULT];
	            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $roles);
	            /** @var GuardAuthenticatorHandler $authenticationGuardHandler */
	            $authenticationGuardHandler = $this->get('security.authentication.guard_handler');
	            $authenticationGuardHandler->authenticateWithToken($token, $request);
	            // Redirect to dashboard uri
	            $route = $this->getTargetPath($request->getSession(), 'main') ?? $request->getSchemeAndHttpHost();
	            return $this->redirect($route);
	        }
	    }
	    
	    return $this->redirect($request->getSchemeAndHttpHost());
	}
}