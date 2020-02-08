<?php

namespace Osds\Backoffice\Application\Update\StaticPage;

use Osds\Backoffice\Infrastructure\Helpers\Path;
use Osds\DDDCommon\Infrastructure\Export\ExportUrlToHTML;
use Osds\DDDCommon\Infrastructure\Helpers\Server;

class UpdateStaticPageUseCase
{

    public function execute($data)
    {
        $domainData = Server::getDomainInfo();
        $baseDestinyPath = Path::getPath('static_pages_cache', $domainData['snakedId'], true, true);
        $baseOriginUrl = $domainData['protocol'] . '://' . $domainData['mainDomain'];

        $exportUrlToHtmlService = new ExportUrlToHTML();

        #is a multilanguage page?
        $multilanguagePage = json_decode($data['post']['seo_name'], true);
        if(is_array($multilanguagePage)) {
            foreach($multilanguagePage as $language => $pageName) {
                $exportUrlToHtmlService->store(
                    "{$baseOriginUrl}/{$pageName}?reloadOsdsCache&lang={$language}",
                    $baseDestinyPath . $pageName
                );
            }
        } else {
            $pageName = $data['post']['seo_name'];
            $exportUrlToHtmlService->store(
                "{$baseOriginUrl}/{$pageName}?reloadOsdsCache",
                $baseDestinyPath . $pageName
            );
        }

        return $data;
    }

}

