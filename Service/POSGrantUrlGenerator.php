<?php

namespace Puzzle\ConnectBundle\Service;

/**
 * Puzzle OAuth Server Grant Url Generator
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class POSGrantUrlGenerator
{
    /**
     * @var array
     */
    private $config;
    
    /**
     * @param array $config
     */
    public function __construct(array $config){
        $this->config = $config;
    }
    
	/**
	 * Generate Login URL for Authorization Code
	 * 
	 * @param string $scope
	 * @param string $redirectUri
	 * @return string
	 */
	public function generateLoginUrl(string $scope, string $redirectUri) {
	    if(session_status() != PHP_SESSION_ACTIVE){
	        session_start();
	    }
	    
	    $_SESSION['state'] = md5(uniqid(mt_rand(), true));
	    
	    $url = $this->config['base_authorize_uri'].'?client_id='.$this->config['client_id'].'&response_type=code&redirect_uri='.$redirectUri.'&scope='.$scope.'&state='.$_SESSION['state'];
	    $url .= isset($this->config['interne']) ? '&interne='.$this->config['interne'] : '';
	    
	    return $url;
	}
	
	/**
	 * Authorization Code Grant URL
	 * 
	 * @param string $code
	 * @param string $redirectUri
	 * @return string
	 */
	public function generateAuthorizationCodeUrl(string $code, string $redirectUri = null) {
	    $redirectUri = $redirectUri ?? $this->config['default_redirect_uri'];
	    
	    $url = $this->config['base_token_uri'].'?client_id='.$this->config['client_id'].'&client_secret='.$this->config['client_secret'].'&grant_type=authorization_code&redirect_uri='.$redirectUri.'&code='.$code;
	    $url .= isset($this->config['interne']) ? '&interne='.$this->config['interne'] : '';
	    
	    return $url;
	}
	
	/**
	 * Implicit Grant
	 *
	 * @param array $config
	 * @return string
	 */
	public function generateImplicitUrl(string $redirectUri = null) {
	    if(session_status() != PHP_SESSION_ACTIVE){
	        session_start();
	    }
	    
	    $_SESSION['state'] = md5(uniqid(mt_rand(), true));
	    
	    $redirectUri = $redirectUri ?? $this->config['default_redirect_uri'];
	    
	    $url = $this->config['base_authorize_uri'].'?client_id='.$this->config['client_id'].'&response_type=token&redirect_uri='.$redirectUri.'&state='.$_SESSION['state'];
	    $url .= isset($this->config['interne']) ? '&interne='.$this->config['interne'] : '';
	    
	    return $url;
	}
	
	/**
	 * URL for generating tokens in OAuth2 Password Grant
	 * 
	 * @param string $username
	 * @param string $password
	 * @param string $redirectUri
	 * @return string
	 */
	public function generatePasswordUrl(string $username, string $password, string $redirectUri = null) {
	    $redirectUri = $redirectUri ?? $this->config['default_redirect_uri'];
	    return $this->config['base_token_uri'].'?client_id='.$this->config['client_id'].'&client_secret='.$this->config['client_secret'].'&grant_type=password&username='.$username.'&password='.$password.'&redirect_uri='.$redirectUri;
	}
	
	/**
	 * Credentials Grant
	 *
	 * @param array $config
	 * @return string
	 */
	public function getClientCrendentialsUrl(string $scope = null, string $redirectUri = null) {
	    $scope = $scope ?? $this->config['default_scope'];
	    $redirectUri = $redirectUri ?? $this->config['default_redirect_uri'];
	    
	    return $this->config['base_token_uri'].'?client_id='.$this->config['client_id'].'&client_secret='.$this->config['client_secret'].'&grant_type=client_credentials&scope='.$scope.'&redirect_uri='.$redirectUri;
	}
	
	
	/**
	 * Refresk Token
	 * 
	 * @param string $refreshToken
	 * @return string
	 */
	public function generateRefreshTokenUrl(string $refreshToken) {
	    return $this->config['base_authorize_uri'].'?client_id='.$this->config['client_id'].'&client_secret='.$this->config['client_secret'].'&grant_type=refresh_token&refresh_token='.$refreshToken;
	}
	
}