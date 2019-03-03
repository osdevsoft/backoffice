<?php

namespace Osds\Backoffice\UI\Search;

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
        SearchEntityQueryBus $query_bus
    )
    {
        $this->query_bus = $query_bus;

        parent::__construct();
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
        $this->build();

        $message_object = $this->getEntityMessageObject($entity, $this->request);

        $data = $this->query_bus->ask($message_object);

        #$data = $this->preTreatDataBeforeDisplaying($entity, $data);

        return $this->generateView('list', $entity, $data);

    }

    public function getEntityMessageObject($entity, $request)
    {
        if (isset($this->config)
            && isset($this->config['domain_structure'])
            && isset($this->config['domain_structure']['models'][$entity])
            && isset($this->config['domain_structure']['models'][$entity]['fields'])
            && isset($this->config['domain_structure']['models'][$entity]['fields']['in_list'])
        ) {
           foreach ($this->config['domain_structure']['models'][$entity]['fields']['in_list'] as $listField) {
               if (strstr($listField, '.')) {
                  $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $listField);
               }
           }
           if (isset($gatherEntities)) {
              $request->parameters['get']['referenced_entities'] = implode(',', $gatherEntities);
           }
        }

        if (isset($request->parameters['search_fields'])) {
           $request->parameters['get']['search_fields'] = $request->parameters['search_fields'];
           unset($request->parameters['search_fields']);
        }

        if (isset($request->parameters['query_filters'])) {
            $request->parameters['get']['query_filters'] = $request->parameters['query_filters'];
            unset($request->parameters['query_filters']);
        }
        #pagination
        $request->parameters['get']['query_filters']['page_items'] = $this->config['domain_structure']['pagination']['items_per_page'];


        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );

    }

}