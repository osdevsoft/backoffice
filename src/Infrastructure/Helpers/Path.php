<?php

namespace Osds\Backoffice\Infrastructure\Helpers;

use \Osds\DDDCommon\Infrastructure\Helpers\Path as DDDCommonHelperPath;

class Path extends DDDCommonHelperPath
{

    protected static $paths = [
        'sites_configurations' => '/sites_configurations/%s/user/config/',
        'backoffice_cache' => '/sites_configurations/%s/public/cache/backoffice/',
        'public' => '/public/',
        'localization' => '/vendor/osds/backoffice/assets/localization/',
        'templates' => '../vendor/osds/backoffice/assets/theme/templates'
    ];

}