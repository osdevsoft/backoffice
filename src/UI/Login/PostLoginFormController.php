<?php

namespace Osds\Backoffice\UI\Login;

use Osds\Auth\Infrastructure\UI\StaticClass\Auth;
use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Osds\Backoffice\Application\Search\SearchEntityQuery;

use Osds\DDDCommon\Infrastructure\Helpers\UI;

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

        $requestParameters = $this->request->parameters['post'];
        $authUser = Auth::getUserAuthToken(
            'http://api.osdshub.sandbox/api/',
            $requestParameters['email'],
            $requestParameters['password'],
            'backoffice'
        );

        if ($authUser != null) {
            UI::redirect('/user');
        } else {
            UI::redirect(self::PAGES['session']['login'], 'danger', 'login_ko');
        }

    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request
        );

    }

}