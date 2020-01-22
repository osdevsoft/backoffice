<?php

namespace Osds\Backoffice\UI\Delete;

use Osds\Backoffice\Application\Delete\DeleteEntityCommand;
use Osds\Backoffice\Application\Insert\DeleteEntityCommandBus;
use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class DeleteEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(DeleteEntityCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
    
    /**
     * Deletes an item from the received data and redirects to the view or the list, if it fails
     *
     * @Route(
     *     "/{entity}/{uuid}",
     *     methods={"PUT"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function delete($entity)
    {

        try
        {
            $this->build();

            ### move to UC
            $this->request_data['uri'][] = $id;
            $result = $this->performAction('delete');
            #redirect to detail
            if (isset($result['items'][0]['deleted_id'])) {
                return $this->redirect("/{$model}", "success", "delete_ok");
            } else {
                return $this->redirect("/{$model}", "danger", "delete_ko", $result['items'][0]['error_message']);
            }
        } catch(\Exception $e)
        {
            return $this->redirect("/{$model}", "danger", "delete_ko", $e);
        }


        return true;
        
    }

}