<?php

namespace Osds\Backoffice\Infrastructure\Controllers;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/session")
 */
class LoginController extends BaseController
{

    const VAR_SESSION_NAME = 'backoffice_user_logged';

    public $request_data;

    /**
     * @Route(
     *     "/login",
     *     methods={"GET"}
     * )
     */
    public function login()
    {
        return $this->generateView(null, 'login', 'session');
    }

    /**
     * @Route(
     *     "/login",
     *     methods={"POST"}
     * )
     */
    public function postLogin()
    {
        $this->request_data['get'] = [
            'search_fields' => [
                'email' => $this->request_data['post']['email']
            ]
        ];
        $data = $this->performAction('list', 'user');

        if (isset($data)
            && $data['total_items'] == 1
            && password_verify($this->request_data['post']['password'], $data['items'][0]['password'])
        ) {
            $this->session->put(self::VAR_SESSION_NAME, $data['items'][0]);
            $this->redirect('/');
        } else {
            $this->redirect(self::PAGES['session']['login'], 'danger', 'login_ko');
        }
    }

    public static function checkAuth($session)
    {
        $backoffice_token = $session->get(self::VAR_SESSION_NAME);

        if (!
            ($backoffice_token != null
            && self::isValidToken($backoffice_token)
        )) {
            return false;
        }

        return true;
    }

    public static function isValidToken($backoffice_token)
    {
        return true;
    }

    public function logout()
    {
        $this->session->remove(self::VAR_SESSION_NAME);
        $this->redirect(self::PAGES['session']['login']);
    }

}