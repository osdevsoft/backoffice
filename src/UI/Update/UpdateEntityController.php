<?php

namespace Osds\Backoffice\UI\Update;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Update\UpdateEntityCommandBus;

use Osds\Backoffice\Application\Update\UpdateEntityCommand;

use Osds\DDDCommon\Infrastructure\Helpers\UI;

/**
 * @Route("/")
 */
class UpdateEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        UpdateEntityCommandBus $commandBus
    ) {
        $this->commandBus = $commandBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

    }
    
    /**
     * Updates an item from the received data and redirects to the view or the list, if it fails
     *
     * @Route(
     *     "/{entity}/edit/{uuid}",
     *     methods={"POST"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function update($entity, $uuid)
    {

        try
        {
            $redirectUrl = str_replace('/edit/', '/', $_SERVER['PATH_INFO']);
            $this->build();

            $requestParameters = $this->preTreatBeforeSaving($entity, $this->request->parameters['post']);

            $messageObject = $this->getMessageObject($entity, $uuid, $requestParameters);
            $result = $this->commandBus->dispatch($messageObject);

            $this->lookForServerErrorsOnResponse($result);

            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                return JsonResponse::create($result, 200);
            }

            #redirect to detail
            if (isset($result['items'][0]['upsert_id'])) {
                UI::redirect($redirectUrl, "success", "EDIT_OK");
            } else {
                $message = isset($result['items'][0]['error_message'])?$result['items'][0]['error_message']:'';
                UI::redirect($redirectUrl, "danger", "EDIT_KO", $message);
            }
        } catch(\Exception $e)
        {
            UI::redirect($redirectUrl,  "danger", "EDIT_KO", $e);
        }

        return true;
        
    }
    
    private function getMessageObject($entity, $uuid, $requestParameters)
    {
        foreach($requestParameters as $key => $value) {
            if(is_array($value)) {
                $requestParameters[$key] = implode('%many%', $value);
            }
        }
        return new UpdateEntityCommand(
            $entity,
            $uuid,
            $requestParameters
        );
        
    }
    
}