<?php

namespace Puzzle\ConnectBundle\Service;

use Symfony\Component\Form\FormError;
use GuzzleHttp\Exception\BadResponseException;

class ErrorFactory {
   
    public static function createFormError($form, BadResponseException $e) {
        $content = json_decode($e->getResponse()->getBody()->getContents(), true);
        
        if (true === isset($content['errors']) && false === empty($content['errors'])) {
            foreach ($content['errors'] as $error) {
                if (true === isset($error['message']) && true === isset($error['property']) && true === isset($error['extras'])) {
                    $formError = new FormError($error['message'], null, [], null, $error['extras']['invalidValue'] ?? null);
                    $error['property'] = 'plainPassword' !== $error['property'] ? $error['property'] : 'password';
                    
                    if (true === $form->has($error['property'])) {
                        $child = $form->get($error['property']);
                        $formError->setOrigin($child);
                        $child->addError($formError);
                    } else {
                        $form->addError($formError);
                    }
                }
            }
        } elseif (true === isset($content['error'])) {
            $form->addError(new FormError($content['error']['message']));
        }
        
        return $form;
    }
    
    public static function createDefaultError(BadResponseException $e) {
        $content = json_decode($e->getResponse()->getBody()->getContents(), true);
        return [
            'code' => $content['error']['code'] ?? $content['code'],
            'message' => $content['error']['message'] ?? $content['message'],
            'details' => $content['errors'] ?? null
        ];
    }
}