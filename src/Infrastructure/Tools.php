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
                'backoffice_cache' => '/sites_configurations/%s/public/cache/backoffice/',
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


    public static function loadSiteConfiguration()
    {
        $configuration_path = self::getPath('sites_configurations', 'samplesite_sandbox', true);
        $path_file = $configuration_path . 'backoffice.yml';
        if (!is_file($path_file)) {
            return false;
        }
        return Yaml::parse(file_get_contents($path_file));
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