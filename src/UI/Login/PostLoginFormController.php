<?php

namespace Osds\Backoffice\UI\Login;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Osds\Backoffice\Application\Search\SearchEntityQuery;

use function Osds\Backoffice\Utils\redirect;

/**
 * @Route("/")
 */
class PostLoginFormController extends BaseUIController
{

    private $query_bus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        SearchEntityQueryBus $queryBus
    )
    {
        $this->query_bus = $queryBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

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
            && password_verify($this->request->parameters['post']['password'], $data['items'][0]['password'])
        ) {
            $this->session->insert(self::USER_AUTH_COOKIE, $data['items'][0]);
            redirect('/user');
        } else {
            redirect(self::PAGES['session']['login'], 'danger', 'login_ko');
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