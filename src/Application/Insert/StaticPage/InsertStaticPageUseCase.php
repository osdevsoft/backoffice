<?php

namespace App\Osds\Backoffice\Application\Insert;

use Osds\Backoffice\Infrastructure\Export\ExportToHTMLService;

class InsertStaticPageUseCase
{

    public function execute($data)
    {
        #save into html
        $multilanguage_page = json_decode($data['post']['seo_name'], true);
        if(is_array($multilanguage_page)) {
            foreach($multilanguage_page as $language_page) {
                ExportToHTMLService::execute(get_server_id(), $language_page);
            }
        } else {
            ExportToHTMLService::execute(get_server_id(), $data['post']['seo_name']);
        }

        return true;
    }

}