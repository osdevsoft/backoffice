<?php

namespace Osds\Backoffice\Utils;

use Symfony\Component\Yaml\Yaml;


public function getAlertMessages()
{
    $message = null;

    if (isset($this->request_data['get']['action_message'])) {
        $message = ['message' => $this->request_data['get']['action_message'] ];
        if (isset($this->request_data['get']['action_result'])) {
            $message['type'] = $this->request_data['get']['action_result'];
        } else {
            $message['type'] = 'info';
        }
    }

    return $message;
}

public function redirect($url, $result = null, $message = null, $error = null)
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


public function loadSiteConfiguration()
{
    $this->loadConfigFile('domain_structure');
}


/**
 * Load Configuration File
 */
function loadConfigFile($file)
{
    $path_file = __DIR__ . '/../../../../../../config/backoffice/' . $file . '.yml';
    if (!is_file($path_file)) {
        return false;
    }
    $this->config[$file] = Yaml::parse(file_get_contents($path_file));
}

public function folderSize($path)
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

function getVisitorLanguage()
{
    if (isset($_GET['lang'])
        && in_array($_GET['lang'], $this->config['domain_structure']['languages'])
    ) {
        $this->session->put('visitor_language', $_GET['lang']);
    }
    $this->visitor_language = $this->session->get('visitor_language');

    if ($this->visitor_language == null) {
        $this->visitor_language = 'es-es';
    }
}

