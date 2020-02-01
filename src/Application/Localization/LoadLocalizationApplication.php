<?php

namespace Osds\Backoffice\Application\Localization;

use Osds\Backoffice\Infrastructure\Helpers\Path;

class LoadLocalizationApplication
{

    public function execute()
    {

        $localizationPath = Path::getPath('localization', null, true);

        $literalsFile = $localizationPath . 'es-es.php';

        require_once $literalsFile;

        return $locale;
    }

}