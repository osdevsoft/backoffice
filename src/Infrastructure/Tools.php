<?php

namespace Osds\Backoffice\Infrastructure;

use Symfony\Component\Yaml\Yaml;

class Tools
{

    public static function getVariables()
    {
        return [
            'relativePaths' => [
                'sites_configurations' => '/sites_configurations/%s/user/config/',
                'backoffice_cache' => '/sites_configurations/%s/cache/backoffice/',
                'public' => '/public/',
                'localization' => '/vendor/osds/backoffice/assets/localization/',
                'templates' => '../vendor/osds/backoffice/assets/theme/templates'
            ]
        ];
    }

    public static function getPath($type, $id = null, $full = false, $create = false)
    {
        $classVariables = self::getVariables();

        if($full == true) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/../';
        } else {
            $path = '';
        }
        $path .= sprintf($classVariables['relativePaths'][$type], $id);
        
        if($create) {
            if($full == false) {
                $path = $_SERVER['DOCUMENT_ROOT'] . $path;
            }
            @mkdir($path, 0777, true);
        }
        
        return $path;
    }

    public static function getFullUri($baseUri, $type, $id)
    {
        $classVariables = self::getVariables();

        $uri = $baseUri . sprintf($classVariables['relativePaths'][$type], $id);
        return $uri;
    }

    public static function parseYaml($yaml, $isFile = false)
    {
        if($isFile) {
            return Yaml::parseFile($yaml);
        }
        return Yaml::parse($yaml);
    }


    public static function checkSeoName($value)
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


    public function getAlertMessages($requestParameters)
    {
        $message = null;

        if(isset($requestParameters['action_message']))
        {
            $message = ['message' => $requestParameters['action_message'] ];
            if(isset($requestParameters['action_result']))
            {
                $message['type'] = $requestParameters['action_result'];
            } else {
                $message['type'] = 'info';
            }
        }

        return $message;
    }

    public function redirect($url, $result = null, $message = null, $error = null)
    {
        $locale = $this->loadLocalization($this->vendor_path . 'Localization/');

        $url = '/' . BACKOFFICE_FOLDER . $url;
        if($message != null)
        {
            if(isset($locale[strtoupper($message)]))
            {
                $message = $locale[strtoupper($message)];
            }
            $url .= '?action_message=' . $message;

            if($error != null) {
                if(is_string($error)) {
                    $url .= $error;
                } else {
                    $url .= $error->getMessage() . ' @ ' . basename($error->getFile()) . '::' . $error->getLine();
                }
            }

            if($result != null)
            {
                $url .= '&action_result=' . $result;
            }
        }
        header('Location: ' . $url);
        exit;
    }


    public function folderSize($path) {
        $total_size = 0;
        $files = scandir($path);
        $cleanPath = rtrim($path, '/') . '/';

        foreach ($files as $t) {
            if ($t <> "." && $t <> "..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->foldersize($currentFile);
                    $total_size += $size;
                } else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }
        }
        return $total_size;
    }

    public static function getTemplateJSForTinyMce()
    {
        $templateBlocksPath = $_SERVER['DOCUMENT_ROOT'] . '/../vendor/osds/template-blocks/assets/blocks/';
        $backOfficecachePath = Tools::getPath('backoffice_cache', 'samplesite_sandbox', true, true);
        $templateBlocksCacheFile = $backOfficecachePath . '/tinymce_definitions.json';
        if(
            file_exists($templateBlocksCacheFile)
            && !isset($_REQUEST['reloadCache'])
        ) {
            return file_get_contents($templateBlocksCacheFile);
        }
        $templateBlocksPaths = glob($templateBlocksPath . '*');
        foreach($templateBlocksPaths as $templateBlocksPath) {
            $config = Tools::parseYaml($templateBlocksPath . '/config.yaml', true);
            $block['title'] = $config['name'];
            $block['description'] = $config['description'];
            $block['content'] = file_get_contents($templateBlocksPath . '/template.tpl');
            $blocks[] = $block;
        }
        file_put_contents($templateBlocksCacheFile, json_encode($blocks));

        return json_encode($blocks);
    }

}