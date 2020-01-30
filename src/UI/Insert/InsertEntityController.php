<?php

namespace Osds\Backoffice\UI\Insert;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Insert\InsertEntityCommandBus;

use Osds\Backoffice\Application\Insert\InsertEntityCommand;

use function Osds\Backoffice\Utils\redirect;

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

            #redirect to detail
            if (isset($result['items'][0]['upsert_id'])) {
                return redirect($redirectUrl . $result['items'][0]['upsert_id'], "success", "create_ok");
            } else {
                return redirect($redirectUrl, "danger", "create_ko", $result['items'][0]['error_message']);
            }

        } catch(\Exception $e)
        {
            return redirect($redirectUrl, "danger", "create_ko", $e);
        }

        return true;
        
    }
    
    private function getEntityMessageObject($entity, $requestParameters)
    {
        return new InsertEntityCommand(
            $entity,
            $requestParameters
        );
    }

}