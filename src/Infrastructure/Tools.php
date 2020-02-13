<?php

namespace Osds\Backoffice\Infrastructure;

use Osds\Backoffice\Infrastructure\Helpers\Path;
use Osds\DDDCommon\Infrastructure\Helpers\File;
use Osds\DDDCommon\Infrastructure\Helpers\Server;

class Tools
{

    public static function loadSiteConfiguration()
    {
        $configuration_path = Path::getPath('sites_configurations', Server::getDomainInfo()['snakedId'], true);
        $path_file = $configuration_path . 'backoffice.yml';
        if (!is_file($path_file)) {
            return false;
        }
        return File::parseYaml($path_file, true);
    }

    public static function getTemplateJSForTinyMce()
    {
        $templateBlocksPath = $_SERVER['DOCUMENT_ROOT'] . '/../vendor/osds/template-blocks/assets/blocks/';
        $backOfficecachePath = Path::getPath('backoffice_cache', Server::getDomainInfo()['snakedId'], true, true);
        $templateBlocksCacheFile = $backOfficecachePath . '/tinymce_definitions.json';
        if(
            file_exists($templateBlocksCacheFile)
            && !isset($_REQUEST['reloadOsdsCache'])
        ) {
            return file_get_contents($templateBlocksCacheFile);
        }
        $templateBlocksPaths = glob($templateBlocksPath . '*');
        foreach($templateBlocksPaths as $templateBlocksPath) {
            $config = File::parseYaml($templateBlocksPath . '/config.yaml', true);
            $block['title'] = $config['name'];
            $block['description'] = $config['description'];
            $block['content'] = file_get_contents($templateBlocksPath . '/template.tpl');
            $blocks[] = $block;
        }
        file_put_contents($templateBlocksCacheFile, json_encode($blocks));

        return json_encode($blocks);
    }

    public static function getStylesForTinyMce()
    {
        return '/' . Server::getDomainInfo()['snakedId'] . '/styles/blocks.css';
    }

}