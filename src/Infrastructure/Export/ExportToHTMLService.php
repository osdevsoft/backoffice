<?php

namespace Osds\Backoffice\Infrastructure\Export;

class ExportToHTMLService
{

    public static function execute($domainData, $seo_name, $language = null) {
        $domainData = [
            'mainDomain' => 'http://samplesite.sandbox',
            'snakedId' => 'samplesite_sandbox'
        ];
        $path = $_SERVER['DOCUMENT_ROOT'] . '/../sites_configurations/' . $domainData['snakedId'] . '/public/cache/front/static_pages/';
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $uri = $domainData['mainDomain'] . '/' . $seo_name . '?reloadOsdsCache';
        if($language != null) {
            $uri .= '&lang=' . $language;
        }
        $static_page_contents = file_get_contents($uri);

        $file_name = $path . $seo_name . '.html';
        return file_put_contents($file_name, $static_page_contents);
    }

}