<?php

namespace Osds\Backoffice\UI\Search;

use Osds\Backoffice\UI\Helpers\Session;

use Osds\Backoffice\Application\Search\SearchEntityQuery;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/backoffice")
 */
class SearchEntityController extends BaseUIController
{

    private $query_bus;

    public function __construct(
        SearchEntityQueryBus $query_bus,
        Session $session
    )
    {
        $this->query_bus = $query_bus;

        parent::__construct($session);
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

        $message_object = $this->getEntityMessageObject($entity);
        $data = $this->query_bus->ask($message_object);

        $data = $this->preTreatDataBeforeDisplaying($entity, $data);
        return $this->generateView($entity,'list_react', $data);

    }

    public function getEntityMessageObject($entity)
    {
        if (isset($this->config)
            && isset($this->config['domain_structure'])
            && isset($this->config['domain_structure']['models'][$entity])
            && isset($this->config['domain_structure']['models'][$entity]['fields'])
            && isset($this->config['domain_structure']['models'][$entity]['fields']['in_list'])
        ) {
           #we have referenced fields to display => we have to join them to recover them
           foreach ($this->config['domain_structure']['models'][$entity]['fields']['in_list'] as $listField) {
               if (strstr($listField, '.')) {
                  $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $listField);
               }
           }
           if (isset($gatherEntities)) {
              $this->request->parameters->get['referenced_entities'] = implode(',', $gatherEntities);
           }
        }

        /*
        if (isset($this->request->parameters->get['search_fields'])) {
           $this->request->parameters->get['search_fields'] = $this->request->parameters->get['search_fields'];
           unset($this->request->parameters->get['search_fields']);
        }

        if (isset($this->request->parameters->get['query_filters'])) {
            $this->request->parameters->get['query_filters'] = $this->request->parameters->get['query_filters'];
            unset($this->request->parameters->get['query_filters']);
        }
        */
        #pagination
        $this->request->parameters->get['query_filters']['page_items'] = $this->config['domain_structure']['pagination']['items_per_page'];


        return new SearchEntityQuery(
            $entity,
            $this->request->parameters
        );

    }

}