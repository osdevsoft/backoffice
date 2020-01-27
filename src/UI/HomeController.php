<?php

namespace Osds\Backoffice\UI;

use Osds\Backoffice\UI\Helpers\Session;

use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class HomeController extends BaseUIController
{

    private $queryBus;

    public function __construct(
        Session $session
    )
    {
        parent::__construct($session);
    }

    /**
     * Displays home
     *
     * @Route(
     *     "/",
     *     methods={"GET"}
     * )
     *
     * @return mixed
     */
    public function home()
    {

        die('Welcome');
        exit;

    }

}