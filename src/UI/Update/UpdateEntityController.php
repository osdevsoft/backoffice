<?php

namespace Osds\Backoffice\UI\Update;

use Osds\Backoffice\Application\Update\UpdateEntityCommand;
use Osds\Backoffice\Application\Update\UpdateEntityCommandBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/backoffice")
 */
class UpdateEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(UpdateEntityCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
    
    /**
     * Updates an item from the received data and redirects to the view or the list, if it fails
     *
     * @Route(
     *     "/{entity}/{uuid}",
     *     methods={"PUT"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function update($entity)
    {

        try
        {
            $this->build();

            $this->preTreatBeforeSaving($model);

            $this->request_data['uri'][] = $id;
            $result = $this->performAction('update');
            #redirect to detail
            if (isset($result['items'][0]['upsert_id'])) {
                return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "success", "edit_ok");
            } else {
                return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "danger", "edit_ko", $result['items'][0]['error_message']);
            }
        } catch(\Exception $e)
        {
            return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "danger", "edit_ko", $e);
        }


        return true;
        
    }

}