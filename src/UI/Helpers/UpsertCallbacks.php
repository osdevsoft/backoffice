<?php

namespace Osds\Backoffice\UI\Helpers;

/**
 * Trait CallbacksTrait
 *
 * Has all the callbacks than can be applied to a field
 *
 * @package Osds\Backoffice\Classes
 */

trait UpsertCallbacks {

    private function checkSeoName($value)
    {
        $from = array(
            'á','À','Á','Â','Ã','Ä','Å',
            'ß','Ç',
            'é','è','ë','È','É','Ê','Ë',
            'í','ì','ï','Ì','Í','Î','Ï','Ñ',
            'ó','ò','ö','Ò','Ó','Ô','Õ','Ö',
            'ú','ù','ü','Ù','Ú','Û','Ü');

        $to = array(
            'a','A','A','A','A','A','A',
            'B','C',
            'e','e','e','E','E','E','E',
            'i','i','i','I','I','I','I','N',
            'o','o','o','O','O','O','O','O',
            'u','u','u','U','U','U','U');

        $value = str_replace($from, $to, $value);
        $value = str_replace(' ', '-', $value);
        $value = strtolower($value);
        $words = preg_split("#[^a-z0-9]#", $value, -1, PREG_SPLIT_NO_EMPTY);
        return implode("-", $words);
    }

    private function crypt($value)
    {
        #already encripted
        if(strstr($value, '$2y$10$')) return $value;
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => 10]);
    }

}