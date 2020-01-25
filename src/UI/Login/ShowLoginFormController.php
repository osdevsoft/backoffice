<?php

namespace Osds\Backoffice\UI\Login;

use Symfony\Component\Routing\Annotation\Route;

use Osds\Backoffice\UI\BaseUIController;

/**
 * @Route("/")
 */
class ShowLoginFormController extends BaseUIController
{

     /**
     * @Route(
     *     "/session/login",
     *     methods={"GET"},
     * )

     */
    public function handle()
    {
        return $this->generateView(null, 'session/login');
    }

}