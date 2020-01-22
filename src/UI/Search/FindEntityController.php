<?php

namespace Osds\Backoffice\UI\Search;

use Osds\Backoffice\UI\Helpers\Session;

use Osds\Backoffice\Application\Search\SearchEntityQuery;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class FindEntityController extends BaseUIController
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
     * Detailed view of an item
     *
     * @Route(
     *     "/{entity}/{uuid}",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function find($entity, $uuid)
    {

        if (isset($this->config)
            && isset($this->config['domain_structure'])
            && isset($this->config['domain_structure']['entities'][$entity])
            && isset($this->config['domain_structure']['entities'][$entity]['fields'])
            && isset($this->config['domain_structure']['entities'][$entity]['fields']['in_detail'])
        ) {
            #we have referenced fields to display => we have to join them to recover them
            foreach ($this->config['domain_structure']['entities'][$entity]['fields']['in_detail'] as $listField) {
                if (strstr($listField, '.')) {
                    $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $listField);
                }
            }
            if (isset($gatherEntities)) {
                $this->request->parameters->get['referenced_entities'] = implode(',', $gatherEntities);
            }
        }

        $this->request->parameters->get['search_fields']['uuid'] = $uuid;

        $message_object = $this->getEntityMessageObject($entity, $this->request);
        $data = $this->query_bus->ask($message_object);

        $data = $this->preTreatDataBeforeDisplaying($entity, $data);
        return $this->generateView($entity,'detail', $data);

//        $referenced_contents['twig_vars'] = $this->getReferencedContents($data['schema'], $model);
//        $data = array_merge($data, $referenced_contents);
    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );

    }

}