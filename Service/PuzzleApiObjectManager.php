<?php

namespace Puzzle\ConnectBundle\Service;


class PuzzleApiObjectManager {
   
    /**
     * Hydratate fields
     * 
     * @param array $fields
     * @param array $data
     * @return array
     */
    public static function hydratate(array $fields, array $data) {
        $array = [];
        foreach ($fields as $field) {
            $array[$field] = $data[$field] ?? '';
        }
        
        return $array;
    }
    
    /**
     * Sanitize data
     * 
     * @param array $data
     * @return array
     */
    public static function sanitize(array $data) {
        foreach ($data as $key => $item) {
            if (! $item) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
}