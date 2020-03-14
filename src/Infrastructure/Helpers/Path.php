<?php

namespace Osds\Backoffice\Infrastructure\Helpers;

use \Osds\DDDCommon\Infrastructure\Helpers\Path as DDDCommonHelperPath;

class Path extends DDDCommonHelperPath
{

    protected static $paths = [
        'sites_configurations' => '/sites_configurations/sites/%s/user/config/',
        'public_resources' => '/sites_configurations/sites/%s/public/',
        'static_pages_cache' => '/sites_configurations/sites/%s/public/cache/front/static_pages/',
        'backoffice_cache' => '/sites_configurations/sites/%s/public/cache/backoffice/',
        'public' => '/public/',
        'sitePublicPath' => '/sites_configurations/sites/%s/public/',
        'localization' => '/vendor/osds/backoffice/assets/localization/',
        'templates' => '../vendor/osds/backoffice/assets/theme/templates'
    ];

}