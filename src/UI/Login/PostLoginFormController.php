<?php

namespace Osds\Backoffice\UI\Login;

use Osds\Backoffice\Application\Search\SearchEntityQuery;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class PostLoginFormController extends BaseUIController
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
     * @Route(
     *     "/session/login",
     *     methods={"POST"},
     * )

     */
    public function handle()
    {

        $this->build();

        $message_object = $this->getEntityMessageObject('user', $this->request);

        $data = $this->query_bus->ask($message_object);

        if (isset($data)
            && $data['total_items'] == 1
            && password_verify($this->request->parameters['password'], $data['items'][0]['password'])
        ) {
            $this->session->put(self::VAR_SESSION_NAME, $data['items'][0]);
            $this->redirect('/user');
        } else {
            $this->redirect(self::PAGES['session']['login'], 'danger', 'login_ko');
        }

    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );

    }

}