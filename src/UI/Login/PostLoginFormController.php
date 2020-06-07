<?php

namespace Osds\Backoffice\UI\Login;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;

use Osds\Auth\Infrastructure\UI\UserAuth;

use Osds\DDDCommon\Infrastructure\Helpers\UI;

/**
 * @Route("/")
 */
class PostLoginFormController extends BaseUIController
{

    private $userAuth;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        UserAuth $userAuth
    )
    {
        $this->userAuth = $userAuth;

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
        $authUser = $this->userAuth->getUserAuthToken(
            $this->session,
            $requestParameters['email'],
            $requestParameters['password'],
            'backoffice'
        );

        if ($authUser != null) {
            $main_entity = $this->config['backoffice']['main_entity'];
            UI::redirect('/' . $main_entity);
        } else {
            UI::redirect(self::PAGES['session']['login'], 'danger', 'login_ko');
        }

    }

}