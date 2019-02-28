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

        #pagination
        #$this->request_data['get']['query_filters']['page_items'] = $this->config['domain_structure']['pagination']['items_per_page'];
        $message_object = $this->getEntityMessageObject($entity, $this->request);

        $data = $this->query_bus->ask($message_object);

        #$data = $this->preTreatDataBeforeDisplaying($entity, $data);

        return $this->generateView('list', $entity, $data);

    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );

    }

}