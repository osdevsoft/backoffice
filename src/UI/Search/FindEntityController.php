<?php

namespace Osds\Backoffice\UI\Search;

use Osds\Backoffice\Infrastructure\Tools;
use Symfony\Component\Routing\Annotation\Route;
use Osds\Backoffice\UI\BaseUIController;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;
use Osds\Backoffice\Application\Search\SearchEntityQueryBus;

use Osds\Backoffice\Application\Search\SearchEntityQuery;

use function Osds\Backoffice\Utils\getAlertMessages;

/**
 * @Route("/")
 */
class FindEntityController extends BaseUIController
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
     * Detailed view of an item
     *
     * @Route(
     *     "/{entity}/{uuid}",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function find($entity, $uuid)
    {

        if (isset($this->config)
            && isset($this->config['backoffice'])
            && isset($this->config['backoffice']['entities'][$entity])
            && isset($this->config['backoffice']['entities'][$entity]['fields'])
            && isset($this->config['backoffice']['entities'][$entity]['fields']['in_detail'])
        ) {
            #we have referenced fields to display => we have to join them to recover them
                foreach ($this->config['backoffice']['entities'][$entity]['fields']['in_detail'] as $detailField) {
                if (strstr($detailField, '.')) {
                    $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $detailField);
                }
            }
            if (isset($gatherEntities)) {
                $this->request->parameters['get']['referenced_entities'] = implode(',', $gatherEntities);
                $this->request->parameters['get']['referenced_entities_contents'] = implode(',', $gatherEntities);
            }
        }

        $this->request->parameters['get']['search_fields']['uuid'] = $uuid;

        $message_object = $this->getEntityMessageObject($entity, $this->request);
        $data = $this->queryBus->ask($message_object);

        $data = $this->preTreatDataBeforeDisplaying($entity, $data);

        $this->setViewVariables($entity, $data);

        $this->view->setTemplate('actions/detail');

        $this->view->render();

//        $referenced_contents['twig_vars'] = $this->getReferencedContents($data['schema'], $model);
//        $data = array_merge($data, $referenced_contents);
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
        $this->view->setVariable('entities_list', $this->config['backoffice']['entities']);
        $this->view->setVariable('action', 'detail');

        $this->view->setVariable('total_items', isset($data['total_items']) ? $data['total_items'] : null);
        $this->view->setVariable('data', isset($data['items']) ? $data['items'] : null);
        $this->view->setVariable('schema', isset($data['schema']) ? $data['schema'] : null);
        $this->view->setVariable('referenced_entities_contents', isset($data['referenced_entities_contents']) ? $data['referenced_entities_contents'] : null);

        $this->view->setVariable('GET', $this->request->parameters['get']);
        $this->view->setVariable('alert_message', getAlertMessages($this->request));

        if (!empty($this->request->parameters['get']) && !empty($this->request->parameters['get']['search_fields'])) {
            $this->view->setVariable('search_fields', $this->request->parameters['get']['search_fields']);
            $this->view->setVariable('query_string_search_fields',
                http_build_query(['search_fields' => $this->request->parameters['get']['search_fields']]));
        }

//        $pagination_variables = $this->view->generatePagination($data['total_items'],
//            $this->config['backoffice']['pagination']);
//        $this->view->setVariable('paginator', $pagination_variables['paginator']);
//        $this->view->setVariable('items_per_page', $pagination_variables['items_per_page']);

        $this->view->setVariable('theme_blocks_json', Tools::getTemplateJSForTinyMce());
    }


}