<?php

namespace Osds\Backoffice\Infrastructure\Export;

class ExportToHTMLService
{

    public static function execute($origin, $destiny) {
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