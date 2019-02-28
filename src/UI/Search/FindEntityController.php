<?php

namespace Osds\Backoffice\UI\Search;

use Osds\Backoffice\Application\Search\SearchEntityQuery;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/backoffice")
 */
class FindEntityController extends BaseUIController
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
    public function find($entity)
    {

        $this->build();
        #we need to get the referenced contents in order to list them on the item form (to be able to list them)

        #add the ID of the item as a filter to the API
        $this->request_data['uri'][] = $id;
        $this->request_data['get']['get_referenced'] = true;
        #gather fields that are other models contents
        if (isset($this->models[$model]['fields']['fillable'])) {
            foreach ($this->models[$model]['fields']['fillable'] as $fillable_field) {
                if (strstr($fillable_field, '.')) {
                    list($required_model, $required_field) = explode('.', $fillable_field);
                    $this->request_data['get']['get_models_contents'][] = $required_model;
                }
            }
        }
        $data = $this->performAction('list');
        $data = $this->preTreatDataBeforeDisplaying($model, $data);

        unset($this->request_data['uri']);

//        $referenced_contents['twig_vars'] = $this->getReferencedContents($data['schema'], $model);
//        $data = array_merge($data, $referenced_contents);

        return $this->generateView($data);
    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );

    }

}