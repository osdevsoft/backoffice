<?php

namespace Osds\Backoffice\UI\Insert;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Insert\InsertEntityCommandBus;

use Osds\Backoffice\Application\Insert\InsertEntityCommand;

use Osds\DDDCommon\Infrastructure\Helpers\UI;

/**
 * @Route("/")
 */
class InsertEntityController extends BaseUIController
{

    private $commandBus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        InsertEntityCommandBus $commandBus
    )
    {
        $this->commandBus = $commandBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

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
            $redirectUrl = str_replace('/create', '/', $_SERVER['PATH_INFO']);
            $this->build();

            $requestParameters = $this->preTreatBeforeSaving($entity, $this->request->parameters['post']);
            $messageObject = $this->getEntityMessageObject($entity, $requestParameters);
            $result = $this->commandBus->dispatch($messageObject);

            $this->lookForServerErrorsOnResponse($result);

            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                return JsonResponse::create($result, 200);
            }
            #redirect to detail
            if (isset($result['items'][0]['upsert_id'])) {
                UI::redirect($redirectUrl . $result['items'][0]['upsert_id'], "success", "CREATE_OK");
            } else {
                $message = isset($result['items'][0]['error_message'])?$result['items'][0]['error_message']:'';
                UI::redirect($redirectUrl, "danger", "CREATE_KO", $message);
            }

        } catch(\Exception $e)
        {
            UI::redirect($redirectUrl, "danger", "CREATE_KO", $e);
        }

        return true;
        
    }
    
    private function getEntityMessageObject($entity, $requestParameters)
    {
        foreach($requestParameters as $key => $value) {
            if(is_array($value)) {
                $requestParameters[$key] = implode('%many%', $value);
            }
        }
        return new InsertEntityCommand(
            $entity,
            $requestParameters
        );
    }

}