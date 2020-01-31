<?php

namespace Osds\Backoffice\Application\Update\StaticPage;

use Osds\Backoffice\Infrastructure\Export\ExportToHTMLService;

class UpdateStaticPageUseCase
{

    public function execute($data)
    {
        #save into html
        $multilanguage_page = json_decode($data['post']['seo_name'], true);
        if(is_array($multilanguage_page)) {
            foreach($multilanguage_page as $language => $language_page) {
                ExportToHTMLService::execute(getDomainData(), $language_page, $language);
            }
        } else {
            ExportToHTMLService::execute(getDomainData(), $data['post']['seo_name']);
        }

        return $data;
    }

}

function getDomainData()
{
//    if(!isset($_REQUEST['domain'])) die('you must send $domain get variable');
//    return $_REQUEST['domain'];

    $requestOrigin = $_SERVER['SERVER_NAME'];
    
    $domainData = [
        'requestOrigin' => $requestOrigin,
        'mainDomain' => '',
        'snakedId' => ''
    ];
    $domainData['mainDomain'] = preg_replace('/^backoffice./','', $requestOrigin);
    $domainData['snakedId'] = str_replace('www.','', $domainData['mainDomain']);
//    $domainData['snakedId'] = preg_replace('/.sandbox$/','', $domain);
    $domainData['snakedId'] = preg_replace('/[^a-zA-Z0-9]/', '_', $domainData['snakedId']);

    return $domainData;
}