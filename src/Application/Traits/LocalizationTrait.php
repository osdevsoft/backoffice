<?php

namespace Osds\Backoffice\Application\Traits;

trait LocalizationTrait
{

    public function loadLocalization($path)
    {
        if (isset($this->config['backoffice']['language'])) {
            $user_language = $this->config['backoffice']['language'];
        } else {
            $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $user_language = strtolower(array_shift($lang));
            $language = explode('-', $user_language);
            $user_language = ( count($language)==1 ) ? $user_language.'-'.$user_language : $user_language;
        }

        $literals_file = $path . $user_language . '.php';
        if (!file_exists($literals_file)) {
            $literals_file = $path . $user_language . '.php';
        }
        require_once $literals_file;

        return $locale;
    }

}