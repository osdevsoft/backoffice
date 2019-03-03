<?php

namespace Osds\Backoffice\Utils;

use Symfony\Component\Yaml\Yaml;


function getAlertMessages($request_data)
{
    $message = null;

    if (isset($request_data->parameters['action_message'])) {
        $message = ['message' => $request_data->parameters['action_message'] ];
        if (isset($request_data->parameters['action_result'])) {
            $message['type'] = $request_data->parameters['action_result'];
        } else {
            $message['type'] = 'info';
        }
    }

    return $message;
}

function redirect($url, $result = null, $message = null, $error = null)
{
    $locale = $this->loadLocalization($this->vendor_path . '/assets/localization/');

    $url = '/' . getenv('BACKOFFICE_FOLDER') . $url;

    if ($message != null) {
        if (isset($locale[strtoupper($message)])) {
            $message = $locale[strtoupper($message)];
        }
        $url .= '?action_message=' . $message;

        if ($error != null) {
            $url .= '<br>';
            if (is_string($error)) {
                $url .= $error;
            } else {
                $url .= $error->getMessage() . ' @ ' . basename($error->getFile()) . '::' . $error->getLine();
            }
        }

        if ($result != null) {
            $url .= '&action_result=' . $result;
        }
    }
    header('Location: ' . $url);
    exit;
}


function loadSiteConfiguration()
{
    $path_file = __DIR__ . '/../../../../config/backoffice/domain_structure.yml';
    if (!is_file($path_file)) {
        return false;
    }
    return Yaml::parse(file_get_contents($path_file));
}



function folderSize($path)
{
    $total_size = 0;
    $files = scandir($path);
    $cleanPath = rtrim($path, '/') . '/';

    foreach ($files as $t) {
        if ($t <> "." && $t <> "..") {
            $currentFile = $cleanPath . $t;
            if (is_dir($currentFile)) {
                $size = $this->foldersize($currentFile);
                $total_size += $size;
            } else {
                $size = filesize($currentFile);
                $total_size += $size;
            }
        }
    }
    return $total_size;
}

function isMultilanguageField($field)
{
    return
        is_array($field)
        && array_keys($field) == $this->config['domain_structure']['languages']
        ;
}

