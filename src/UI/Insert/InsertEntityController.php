<?php

namespace Osds\Backoffice\UI\Insert;

use Osds\Backoffice\Application\Insert\InsertEntityCommand;
use Osds\Backoffice\Application\Insert\InsertEntityCommandBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/backoffice")
 */
class InsertEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(InsertEntityCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
    
    /**
     * Creates an item from the received data and redirects to the view or the list, if it fails
     *
     * @Route(
     *     "/{entity}/create",
     *     methods={"POST"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function insert($entity)
    {

        try
        {
            $this->build();

            $this->preTreatBeforeSaving($entity);

            $result = $this->performAction('create');
            if (isset($result['items'][0]['upsert_id'])) {
                return $this->redirect("/{$model}/edit/{$result['items'][0]['upsert_id']}", "success", "create_ok");
            } else {
                return $this->redirect("/{$model}", "warning", $result['error_message']);
            }

        } catch(\Exception $e)
        {
            $this->redirect("/{$model}", "danger", "create_ko", $e);
        }

        return true;
        
    }

}