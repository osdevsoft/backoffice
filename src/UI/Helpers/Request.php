<?php
namespace Osds\Backoffice\UI\Helpers;

use GuzzleHttp\Client as HttpClient;
use Osds\Backoffice\Infrastructure\Controllers\LoginController;

/**
 * Class used to make HTTP requests
 *
 * Class Request
 * @package Osds\LaravelPublic\Classes
 */

class Request
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $event = null;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * Constructor.
     *
     * @param string          $url        The API url
     * @param string          $method     The HTTP method
     * @param array           $data       The parameters
     * @param array           $headers    The HTTP headers
     */
    public function __construct($url = null, $method = null, $data = null, array $headers = array(), $query_string_params = array())
    {
        $this->setUrl($url);
        $this->method = $method;
        $this->data = $data;
        $this->headers = $headers;

    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }


    public function sendRequest()
    {

        $api_url = '';
        if (strpos(getenv('API_URL'), 'http') === false) {
            $api_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        }
        $api_url .= getenv('API_URL');

        $client = new HttpClient([
            'base_uri' => $api_url,
            'headers' => [
                'Accept' => 'application/json',
                'X-Auth-Token' => 'PublicTokenForRequestingAPI'
            ],
                'verify' => false
        ]);

        try {
            if (isset($this->data->multipart)) {
                // There is a file to be sent
                foreach ($this->data->multipart as $key => $file) {
                    $file_data = [
                        'name'      => $key . '##' . $file['persistence']['repository'] . '|' . $file['persistence']['parameters']['folder'],
                        'contents'  => $file['content'],
                        'filename'  => $file['name'],
                    ];
                    $this->options['multipart'][] = $file_data;
                }

                foreach ($this->data->post as $key => $value) {
                    $field_data = [
                        'name'      => $key,
                        'contents'  => $value,
                    ];
                    $this->options['multipart'][] = $field_data;
                }
            } elseif (isset($this->data->post)) {
                $this->options['form_params'] = $this->data->post;
            }

            if (isset($this->data->uri) && count($this->data->uri) > 0) {
                $this->url .= '/' . implode('/', $this->data->uri);
            }

            $this->url .= '?';
            /*$session = new Session();
            if ($user = $session->get(LoginController::VAR_SESSION_NAME)) {
                $this->url .= '&user_id=' . $user['uuid'];
            }*/
            if (isset($this->data->get) && count($this->data->get) > 0) {
                $this->url .= '&' . http_build_query($this->data->get);
            }
            if ($this->event != null) {
                $this->url .= '?log_event=' . $this->event;
            }

            $response = $client->request(
                $this->method,
                $this->url,
                $this->options
            );

            #request has been sent, clean it
            unset($this->data);
        } catch (\Throwable $throwable) {
            throw new \Exception($throwable);
        }
        try {
            $data = json_decode($response->getBody());

            if (is_null($data)) {
                $data = $response->getBody();
            }
        } catch (\Exception $e) {
            $data = $response->getBody();
        }

        return json_decode(json_encode($data), true);

    }

}