<?php

namespace Osds\Backoffice\UI\Search;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Osds\Backoffice\Application\Search\SearchEntityQuery;

use Osds\DDDCommon\Infrastructure\Helpers\UI;

/**
 * @Route("/")
 */
class SearchEntityController extends BaseUIController
{

    private $queryBus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        SearchEntityQueryBus $queryBus
    )
    {
        $this->queryBus = $queryBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

    }

    /**
     * Lists an entity items
     *
     * @Route(
     *     "/{entity}",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function search($entity)
    {
        $messageObject = $this->getEntityMessageObject($entity);
        $data = $this->queryBus->ask($messageObject);

        $this->lookForServerErrorsOnResponse($data);

        $this->setViewVariables($entity, $data);

        $this->view->setTemplate('actions/list');
        $this->view->render();

    }

    public function getEntityMessageObject($entity)
    {
        if (isset($this->config)
            && isset($this->config['backoffice'])
            && isset($this->config['backoffice']['entities'][$entity])
            && isset($this->config['backoffice']['entities'][$entity]['fields'])
            && isset($this->config['backoffice']['entities'][$entity]['fields']['in_list'])
        ) {
           #we have referenced fields to display => we have to join them to recover them
           foreach ($this->config['backoffice']['entities'][$entity]['fields']['in_list'] as $listField) {
               if (strstr($listField, '.')) {
                  $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $listField);
               }
           }
           if (isset($gatherEntities)) {
              $this->request->parameters['get']['referenced_entities'] = implode(',', $gatherEntities);
           }
        }

        #pagination
        $this->request->parameters['get']['query_filters']['page_items'] = $this->config['backoffice']['pagination']['items_per_page'];


        return new SearchEntityQuery(
            $entity,
            $this->request->parameters
        );

    }

    /**
     * @param $entity
     * @param $data
     */
    public function setViewVariables($entity, $data): void
    {
        $this->view->setVariable('entity', $entity);
        $this->view->setVariable('current_entity', $entity);
        $this->view->setVariable('entities_list', $this->config['backoffice']['entities']);
        $this->view->setVariable('action', 'list');

        $this->view->setVariable('total_items', isset($data['total_items']) ? $data['total_items'] : null);
        $this->view->setVariable('data', isset($data['items']) ? $data['items'] : null);
        $this->view->setVariable('schema', isset($data['schema']) ? $data['schema'] : null);
        $this->view->setVariable('required_entities_contents', isset($data['required_entities_contents']) ? $data['required_entities_contents'] : null);

        $this->view->setVariable('GET', $this->request->parameters['get']);
        if($this->view->getVariable('alert_message') == null) {
            $this->view->setVariable('alert_message', UI::getAlertMessages($this->request->parameters));
        }

        if (!empty($this->request->parameters['get']) && !empty($this->request->parameters['get']['search_fields'])) {
            $this->view->setVariable('search_fields', $this->request->parameters['get']['search_fields']);
            $this->view->setVariable('query_string_search_fields',
                http_build_query(['search_fields' => $this->request->parameters['get']['search_fields']]));
        } else {
            $this->view->setVariable('query_string_search_fields', '');
        }
        if(isset($data['total_items'])) {
            $pagination_variables = $this->view->generatePagination(
                $data['total_items'],
                $this->config['backoffice']['pagination']);
            $this->view->setVariable('paginator', $pagination_variables['paginator']);
            $this->view->setVariable('items_per_page', $pagination_variables['items_per_page']);
        }

        #TODO: entity_metadata are the constants defined on the entity
        $this->view->setVariable('entity_metadata', $this->config['backoffice']['entities'][$entity]['fields']['fields_schema']);

        $this->view->setVariable('config', $this->config);
    }

}