<?php

namespace Osds\Backoffice\UI\Delete;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Delete\DeleteEntityCommandBus;

use Osds\DDDCommon\Infrastructure\Helpers\UI;
use Osds\Backoffice\Application\Delete\DeleteEntityCommand;

/**
 * @Route("/")
 */
class DeleteEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        DeleteEntityCommandBus $commandBus
    )
    {
        $this->commandBus = $commandBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

    }

    /**
     * Deletes an item from the received data and redirects to the view or the list, if it fails
     *
     * @Route(
     *     "/{entity}/delete/{uuid}",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function delete($entity, $uuid)
    {

        try
        {
            $redirectUrl = preg_replace('/delete.*/', '', $_SERVER['PATH_INFO']);

            $this->build();

            $requestParameters['uri'][] = $uuid;
            $messageObject = $this->getEntityMessageObject($entity, $requestParameters);
            $result = $this->commandBus->dispatch($messageObject);

            $this->lookForServerErrorsOnResponse($result);

            #redirect to detail
            if (isset($result['items'][0]['deleted_id'])) {
                UI::redirect($redirectUrl, "success", "DELETE_OK");
            } else {
                if(isset($result['items'][0]['error_message'])) {
                    $error = $result['items'][0]['error_message'];
                } else {
                    $error = null;
                }
                UI::redirect($redirectUrl, "danger", "DELETE_KO", $error);
            }
        } catch(\Exception $e)
        {
            UI::redirect($redirectUrl, "danger", "DELETE_KO", $e);
        }

        return true;
        
    }

    private function getEntityMessageObject($entity, $requestParameters)
    {
        return new DeleteEntityCommand(
            $entity,
            $requestParameters
        );
    }

}