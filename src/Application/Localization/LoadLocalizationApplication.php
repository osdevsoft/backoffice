<?php

namespace Osds\Backoffice\Application\Localization;

use Osds\Backoffice\Infrastructure\Tools;

class LoadLocalizationApplication
{

    public function execute()
    {

        $localizationPath = Tools::getPath('localization', null, true);

        $literalsFile = $localizationPath . 'es-es.php';

        require_once $literalsFile;

        return $locale;
    }

}