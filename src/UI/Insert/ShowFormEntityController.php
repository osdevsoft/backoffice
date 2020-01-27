<?php

namespace Osds\Backoffice\UI\Insert;

use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class ShowFormEntityController extends BaseUIController
{

    private $queryBus;

    public function __construct()
    {

    }
    
    /**
     * Create entity form
     *
     * @Route(
     *     "/{entity}/create",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function showForm($entity)
    {

        $this->build();

        #we need them in order to get mandatory references (foreign relations)
        $data['twig_vars'] = $this->getReferencedContents([], $entity);
        return $this->generateView($data, 'create');

    }

}