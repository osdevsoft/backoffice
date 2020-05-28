<?php

namespace Osds\Backoffice\UI\Login;

use Symfony\Component\Routing\Annotation\Route;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;

use Osds\Backoffice\UI\BaseUIController;


/**
 * @Route("/")
 */
class ShowLoginFormController extends BaseUIController
{

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication
    )
    {

        parent::__construct($session, $view, $loadLocalizationApplication);

    }
    
     /**
     * @Route(
     *     "/session/login",
     *     methods={"GET"},
     * )
     * @Route(
     *     "",
     *     methods={"GET"}
     * )
     */
    public function handle()
    {
        $this->view->setTemplate('session/login.twig');
        return $this->view->render();
    }

}