<?php

namespace Osds\Backoffice\Application\StaticPage;

use Osds\Backoffice\Infrastructure\Helpers\Path;
use Osds\DDDCommon\Infrastructure\Helpers\Server;

class ClearStaticPageCache
{

    public function execute()
    {
        $domainInfo = Server::getDomainInfo();
        $cachePath = Path::getPath('static_pages_cache', $domainInfo['snakedId'], true);
        $result = exec("rm {$cachePath}/*.*");

        return $result;
    }

}