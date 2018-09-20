<?php

namespace Puzzle\ConnectBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Puzzle\ConnectBundle\Entity\Token;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Puzzle\ConnectBundle\Service\POSGrantUrlGenerator;

class PuzzleAPIClient {
    
    use TargetPathTrait;
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;
    
    /**
     * @var POSGrantUrlGenerator
     */
    protected $posUrlGenerator;
    
    /**
     * @var string
     */
    protected $baseApisUri;
    
    /**
     * @var string
     */
    protected $apisVersion;
    
    /**
     * @param EntityManagerInterface    $em
     * @param string                    $baseApiUri
     * @param string                    $apisVersion
     */
    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, POSGrantUrlGenerator $posUrlGenerator, string $baseApisUri, string $apisVersion = 'v1') {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->posUrlGenerator = $posUrlGenerator;
        $this->apisVersion = $apisVersion;
        $this->baseApisUri = $baseApisUri;
    }
    
    public function createHttpClient() {
        return new Client(['base_uri' => $this->baseApisUri]);
    }
    
    public function versionedUrl( string $url) {
        return '/'.$this->apisVersion.$url;
    }
    
    /**
     * @param string    $url
     * @param array     $criteria
     * @return mixed
     */
    public function pull (string $url, array $criteria = null) {
        $url = self::versionedUrl($url);
        $url = $criteria || count($criteria) > 0 ? $url.'?'.http_build_query($criteria) : $url;
        $token = $this->getToken(); 
        $response = self::createHttpClient()->get($url, [
            'headers'      => [
                'Accept'           => 'application/json',
                'Content-Type'     => 'application/json',
                'Authorization'    => 'Bearer '.$token->getAccessToken()
            ]
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }
    
    /**
     * @param string    $method
     * @param string    $url
     * @param array     $data
     * @return mixed
     */
    public function push (string $method, string $url, array $data = null) {
        $url = self::versionedUrl($url);
        $token = $this->getToken();
        $options = [
            'headers'      => [
                'Accept'           => 'application/json',
                'Content-Type'     => 'application/json',
                'Authorization'    => 'Bearer '.$token->getAccessToken()
            ]
        ];
        
        if ($data || count($data) > 0) {
            $options['form_params'] = $data;
            $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        
        $response = self::createHttpClient()->request($method, $url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function getToken() {
        $token = $this->em->getRepository(Token::class)->findOneBy([]);
        
        if ($token && $token->getExpiresAt()->getTimestamp() < time()) {
            $array = $this->pull($this->posUrlGenerator->generateRefreshTokenUrl($token->getRefreshToken()));
            
            $token->setAccessToken($array['access_token']);
            $token->setRefreshToken($array['refresh_token']);
            
            $date = new \DateTime();
            $date->setTimestamp(time() + $array['expires_in']);
            $token->setExpiresAt($date);
            
            $this->em->flush(); 
        }
        
        return $token;
    }
}