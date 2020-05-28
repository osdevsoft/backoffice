<?php

namespace Osds\Backoffice\UI\Insert;

use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Osds\Backoffice\Application\Search\SearchEntityQuery;
use Osds\Backoffice\Infrastructure\Tools;
use Osds\DDDCommon\Infrastructure\Helpers\UI;

/**
 * @Route("/")
 */
class ShowFormEntityController extends BaseUIController
{

    private $queryBus;

    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication,
        SearchEntityQueryBus $queryBus
    )
    {
        $this->queryBus = $queryBus;

        parent::__construct($session, $view, $loadLocalizationApplication);

    }
    
    /**
     * Create entity form
     *
     * @Route(
     *     "/{entity}/create",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function showForm($entity)
    {

        $this->build();

        $referencedEntitiesRequest = $this->getReferencedEntitiesToRequest($entity, $this->config);
        $this->request->parameters = array_merge($this->request->parameters, $referencedEntitiesRequest);

        $messageObject = $this->getEntityMessageObject($entity, $this->request);
        $data = $this->queryBus->ask($messageObject);

        $this->lookForServerErrorsOnResponse($data);

        $this->setViewVariables($entity, $data);

        $this->view->setTemplate('actions/create.twig');

        $this->view->render();

    }

    public function getEntityMessageObject($entity, $request)
    {
        return new SearchEntityQuery(
            $entity,
            $request->parameters
        );
    }

    /**
     * @param $entity
     * @param $data
     */
    public function setViewVariables($entity, $data): void
    {
        $this->view->setVariable('config', $this->config);

        $this->view->setVariable('entity', $entity);
        $this->view->setVariable('current_entity', $entity);

        $this->view->setVariable('entities_list', $this->config['backoffice']['entities']);
        $this->view->setVariable('action', 'detail');

        $this->view->setVariable('GET', $this->request->parameters['get']);
        $this->view->setVariable('alert_message', UI::getAlertMessages($this->request->parameters));

        if (!empty($this->request->parameters['get']) && !empty($this->request->parameters['get']['search_fields'])) {
            $this->view->setVariable('search_fields', $this->request->parameters['get']['search_fields']);
            $this->view->setVariable('query_string_search_fields',
                http_build_query(['search_fields' => $this->request->parameters['get']['search_fields']]));
        }

        $this->view->setVariable('entities_metadata', $this->config['backoffice']['entities']);

        $this->view->setVariable('referenced_entities_contents', isset($data['referenced_entities_contents']) ? $data['referenced_entities_contents'] : null);

        $this->view->setVariable('theme_style_sheet', Tools::getStylesForTinyMce());
        $this->view->setVariable('theme_blocks_json', Tools::getTemplateJSForTinyMce());
    }
    
    private function getReferencedContents($entity)
    {
        $entityConfig = $this->config['backoffice'];
        foreach($entityConfig['entities'][$entity]['fields']['fillable'] as $fillableField)
        {
            if(strstr($fillableField, '.')) {
                #field from another entity => recover their values to display them
            }
        }
    }

}