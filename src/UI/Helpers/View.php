<?php

namespace Osds\Backoffice\UI\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Twig_Loader_Filesystem;

trait View
{

    /**
     *
     * Generates the view with the data provided
     *
     * @param array $data
     *                  items => results
     *                  metadata => info of the model (metadata-model-views-type)
     *
     * @param null $action
     * @param string $context
     *
     * @return Response
     */
    public function generateView($view, $entity, $data = null)
    {
        $model = '';

        if (isset($data['twig_vars'])) {
            $this->twig_vars = $data['twig_vars'];
        } else {
            $this->twig_vars = [];
        }

        $this->twig_vars['model'] = $entity;

        $this->twig_vars = $this->loadTwigVariables($data, $view);

        $view = $context . '/';
        $view .= $view;
//        if (view()->exists($model.'/'.$method)) {
//            $view .= $model . '/' . $method;
//        }

        if ($view == 'detail') {
            $twig_vars['views']['detail_actions'] = '/twig_partials/detail/actions';
            #check if we have a custom view for the actions of this model
//            if (view()->exists($model . '/' . $twig_vars['views']['detail_actions'])) {
//                $twig_vars['views']['detail_actions'] = $model . '/' . $twig_vars['views']['detail_actions'];
//            }
        }
        $view = 'actions/list';
        return $this->renderTwigView($view, $this->twig_vars);
    }

    /**
     *
     * Variables we will need on the view
     *
     * @param $data
     * @param $method
     * @param $twig_vars
     * @param $model
     * @return mixed
     */
    private function loadTwigVariables($data, $method, $model)
    {
        $this->loadViewDataTwigVariables($data, $method, $model);
        $this->loadPreviousSearchesTwigVariables();
        $this->loadAlertMessages();
        $this->loadLocales();
        if (isset($data['total_items'])) {
            $this->twig_vars = $this->loadModelDataTwigVariables($model);

            if ($data['total_items'] > 0
                && $data['total_items'] > count($data['items'])
            ) {
                $this->twig_vars = $this->loadPagination($twig_vars, $data['total_items']);
            }
        }

        $this->twig_vars['backoffice_folder'] = 'backoffice';
        $this->twig_vars['config'] = $this->config;

    }

    private function loadViewDataTwigVariables($data, $method, $model)
    {
        #models for navigation
        $this->twig_vars['models_list'] = $this->models;
        $this->twig_vars['model'] = $model;
        #page title and section
        $this->twig_vars['action'] = $method;

        #data itself to show
        $this->twig_vars['data'] = isset($data['items'])?$data['items']:null;
        $this->twig_vars['total_items'] = isset($data['total_items'])?$data['total_items']:null;
        $this->twig_vars['schema'] = isset($data['schema'])?$data['schema']:null;
        if (isset($data['required_models_contents'])) {
             $this->twig_vars['editable_referenced_models_contents'] = $data['required_models_contents'];
        } else {
            $this->twig_vars['editable_referenced_models_contents'] = null;
        }

        #templates for tinymce

//        $twig_vars['theme_blocks_json'] = $this->getTemplateJSForTinyMce();
//        $twig_vars['theme_style_sheet'] = '/styles/' . $this->config['site']['id'] . '.css';

        #data passed on url
        $this->twig_vars['GET'] = $this->request;
    }

    /**
     * If we have performed a previous search, get these variables for displaying them
     *
     * @param $twig_vars
     * @return mixed
     */
    private function loadPreviousSearchesTwigVariables()
    {
        if (!empty($this->request_data['get']) && !empty($this->request_data['get']['search_fields'])) {
            $this->twig_vars['search_fields'] = $this->request_data['get']['search_fields'];
            $this->twig_vars['query_string_search_fields'] = http_build_query(['search_fields' => $this->request_data['get']['search_fields']]);
        }
    }

    /**
     * Possible messages received by query_string
     *
     * @param $twig_vars
     * @return mixed
     */
    private function loadAlertMessages($twig_vars)
    {
        $this->twig_vars['alert_message'] = $this->getAlertMessages();
    }

    /**
     * Pagination navigator vars
     *
     * @param $twig_vars
     * @param $total
     * @return mixed
     */
    public function loadPagination($total)
    {

        if (isset($this->config['domain_structure']['pagination']['display_mode'])) {
            $vars['mode'] = $this->config['domain_structure']['pagination']['display_mode'];
        } else {
            $vars['mode'] = 'pages';
        }

        if (isset($this->config['domain_structure']['pagination']['items_per_page'])) {
            $items_per_page = $this->config['domain_structure']['pagination']['items_per_page'];
        } else {
            $items_per_page = 10;
        }

        if (isset($this->config['domain_structure']['pagination']['pages_per_page'])) {
            $pages_per_page = $this->config['domain_structure']['pagination']['pages_per_page'];
        } else {
            $pages_per_page = 10;
        }

        #total number of pages
        $num_pages = ceil($total / $items_per_page);

        #just one, no need of pagination
        if ($num_pages <= 1) {
            return $this->twig_vars;
        }

        #generate parameter for the url
        preg_match('/query_filters\[page\]=(.*)\/?/i', $_SERVER['REQUEST_URI'], $page_num);
        if (isset($page_num[1])) {
            $current_page = $page_num[1];
            $href = str_replace('query_filters[page]='.$current_page, 'query_filters[page]=%page%', $_SERVER['REQUEST_URI'] );
        } else {
            $current_page = 1;
            $href = $_SERVER['REQUEST_URI'];
            if (strstr($_SERVER['REQUEST_URI'], '?')) {
                $href .= '&';
            } else {
                $href .= '?';
            }
            $href .= 'query_filters[page]=%page%';
        }

        #first page to display in the paging navigator
        $first_page = max(1, $current_page - floor($pages_per_page / 2));
        #last page to display in the paging navigator
        $last_page = min($num_pages, $first_page + $pages_per_page - 1);

        #first page to display on the paging navigator is not the first page => display link to first page
        if ($first_page != 1) {
            $first_page_link = str_replace('%page%', 1, $href);
            $vars['first'] = $first_page_link;
        }

        #we are not on the first page => we need a link to go to the previous page
        if($current_page != 1) {
            $prev_page_link = str_replace('%page%', $current_page - 1, $href);
            $vars['previous'] = $prev_page_link;
        }

        #links to pages
        for ($i=$first_page;$i<=$last_page;$i++) {
            if($i == $current_page) {
                $vars['current_page'] = $i;
            }
            $page_link = str_replace('%page%', $i, $href);
            $vars['pages'][$i] = $page_link;
        }

        #current page is not the last => display a link to the next page
        if($current_page < $num_pages) {
            $next_page_link = str_replace('%page%', $current_page + 1, $href);
            $vars['next'] = $next_page_link;
        }

        #last page to display on the paging navigator is not the last page => display a link to the last page
        if($last_page != $num_pages) {
            $last_page_link = str_replace('%page%', $num_pages, $href);
            $vars['last'] = $last_page_link;
        }

        #paging navigator itself
        $this->twig_vars['paginator'] = $vars;
        #number of items that will be displayed
        $this->twig_vars['items_per_page'] = $items_per_page;

    }


    /**
     * Common texts depending on the browser language (if not set on config)
     *
     * @param $twig_vars
     * @return mixed
     */
    private function loadLocales()
    {
        $path = $this->vendor_path . '/assets/localization/';

        if (isset($this->config['backoffice']['language'])) {
            $user_language = $this->config['backoffice']['language'];
        } else {
            $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $user_language = strtolower(array_shift($lang));
            $language = explode('-', $user_language);
            $user_language = ( count($language)==1 ) ? $user_language.'-'.$user_language : $user_language;
        }

        $literals_file = $path . $user_language . '.php';
        if (!file_exists($literals_file)) {
            $literals_file = $path . 'en-us.php';
        }

        require_once $literals_file;

        $this->twig_vars['locale'] = $locale;
    }


    /**
     * WYSIWYG editor
     *
     * @param $model
     * @return mixed
     */
//    private function getTemplateJSForTinyMce()
//    {
//        $site_id = $this->config['site']['id'];
//        $blocks_path = base_path('sites_configurations/' . $site_id . '/user/layout/templates/blocks/');
//        $theme_blocks = file_get_contents($blocks_path . 'definitions.json');
//        $theme_blocks_array = json_decode($theme_blocks, true);
//        foreach($theme_blocks_array as &$theme_block)
//        {
//            if(isset($theme_block['url']))
//            {
//                $theme_block['content'] = file_get_contents($blocks_path . $theme_block['url']);
//                unset($theme_block['url']);
//            }
//        }
//
//        return json_encode($theme_blocks_array);
//    }



    #To deprecate, recover on first call
    private function loadModelDataTwigVariables($model)
    {
        #it is not possible to call it on constructor, Route::current (on getmetadata command) is null
        $this->twig_vars['models_metadata'] = $this->loadModelMetadata($model)['items'][0];
    }

    /**
     * recover model metadata
     *
     * @param $model
     * @return mixed
     */
    private function loadModelMetadata($model)
    {
        try
        {
            $this->requestRelatedModels($model);
            #return $this->performAction('getmetadata');
        } catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }


    /**
     * @param $model
     */
    private function requestRelatedModels($model)
    {
        if (isset($this->models[$model]['related_models'])) {
            $related_models = [];
            foreach ($this->models[$model]['related_models'] as $related_model) {
                if (strstr($related_model, '.') === false) {
                    $related_models[] = $related_model;
                }
            }
            $this->request_data['get']['related_models'] = implode(',', $related_models);
        }
    }

    private function renderTwigView($view, $params)
    {
        $template_path = '/var/www/osds/backoffice/vendor/osds/backoffice/src/assets/theme/templates/';
//        $loader = new Twig_Loader_Array($params);
        $loader = new Twig_Loader_Filesystem($template_path);
//        $loader->setTemplate('template_to_use', $template_path);
        $twig = new Twig_Environment($loader);

        $html = $twig->render($view . '.twig', $params);

        return new Response($html);
    }
}