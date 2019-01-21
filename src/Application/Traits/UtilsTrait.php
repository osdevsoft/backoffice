<?php

namespace Osds\Backoffice\Application\Traits;

use Symfony\Component\Yaml\Yaml;

trait UtilsTrait
{

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
    private function loadConfigFile($file)
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

    private function isMultilanguageField($field)
    {
        return
            is_array($field)
            && array_keys($field) == $this->config['domain_structure']['languages']
            ;
    }

    private function getVisitorLanguage()
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

    private function preTreatDataBeforeDisplaying($model, $data, $localize = false)
    {

        if (@count($data['items']) > 0) {

            #treat multilanguage fields
            if (isset($this->config['domain_structure']['languages'])
                && isset($this->config['domain_structure']['models'][$model]['schema']['multilanguage_fields'])
            ) {
                foreach ($data['items'] as &$item) {
                    foreach ($this->config['domain_structure']['models'][$model]['schema']['multilanguage_fields'] as $ml_field) {
                        $item[$ml_field] = json_decode($item[$ml_field], true);
                        #preserve only a desired language
                        if ($localize
                            && is_array($item[$ml_field])
                        ) {
                            #check if we have at least one item of the array that is a valid language
                            if (isset($this->visitor_language)
                                && count(array_intersect(array_keys($item[$ml_field]), $this->config['domain_structure']['languages'])) > 0
                                && in_array($this->visitor_language, array_keys($item[$ml_field]))
                            ) {
                                #visitor language has a defined value on the field array
                                $item[$ml_field] = $item[$ml_field][$this->visitor_language];
                            } else {
                                #user language is not defined, use first
                                $item[$ml_field] = current($item[$ml_field]);
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}
