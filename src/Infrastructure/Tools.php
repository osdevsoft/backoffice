<?php

namespace Osds\Backoffice\Infrastructure;

use ScssPhp\ScssPhp\Compiler as ScssPhpCompiler;
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
            if(!file_exists($templateBlocksPath . '/config.yaml')) {
                #it's a subblock
                $templateSubBlocksPaths = glob($templateBlocksPath . '/*');
                foreach($templateSubBlocksPaths as $templateSubBlocksPath) {
                    $config = File::parseYaml($templateSubBlocksPath . '/config.yaml', true);
                    $block['title'] = $config['name'];
                    $block['description'] = $config['description'];
                    $block['content'] = file_get_contents($templateSubBlocksPath . '/template.tpl');
                    $blocks[] = $block;
                }
            } else {
                $config = File::parseYaml($templateBlocksPath . '/config.yaml', true);
                $block['title'] = $config['name'];
                $block['description'] = $config['description'];
                $block['content'] = file_get_contents($templateBlocksPath . '/template.tpl');
                $blocks[] = $block;
            }
        }
        file_put_contents($templateBlocksCacheFile, json_encode($blocks));

        return json_encode($blocks);
    }

    public static function getStylesForTinyMce()
    {
        $backOfficecachePath = Path::getPath('backoffice_cache', Server::getDomainInfo()['snakedId'], true);
        $blocks_defaults_styles = $backOfficecachePath . 'blocks_defaults.css';
        if(!file_exists($blocks_defaults_styles)
            || isset($_REQUEST['reloadOsdsCache'])) {
                #regenerate styles file
                $templateBlocksPath = $_SERVER['DOCUMENT_ROOT'] . '/../vendor/osds/template-blocks/assets/blocks/';
                $templateBlocksScss = self::rglob($templateBlocksPath . 'styles.scss');
                $scss = '';
                foreach($templateBlocksScss as $tbs) {
                    $scss .= file_get_contents($tbs);
                }
                $scss = str_replace('%class%', '', $scss);
                $scssCompiler = new ScssPhpCompiler();
                $compiledScss = $scssCompiler->compile($scss);
                #minimize
                $compiledScss = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $compiledScss);
                $destinyFile = Path::getPath('backoffice_cache', Server::getDomainInfo()['snakedId'], true);
                file_put_contents($destinyFile . 'blocks_defaults.css', $compiledScss);

        }

        return Path::getPath('public', Server::getDomainInfo()['snakedId']);
    }

    public static function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, self::rglob($dir.'/'.basename($pattern), $flags));
    }
return $files;
}

}