<?php

namespace Osds\Api\Framework\Symfony;

use Osds\Backoffice\Infrastructure\Controllers\BaseController;

use Illuminate\Http\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;

use Psr\Log\LoggerInterface;


/**
 * @Route("/api/{entity}")
 */
class SymfonyController extends BaseController
{


    public function __construct(
        Request $request,
        EntityManagerInterface $entity_manager,
        LoggerInterface $logger,
        AwsS3Util $awss3util,
        SimpleEmailServiceClient $awsSes,
        AwsSnsUtil $awsSns
    )
    {
        $this->services = [
            'entity_manager' => $entity_manager,
            'logger' => $logger,
            'awss3util' => $awss3util,
            'awsSes' => $awsSes,
            'awsSns' => $awsSns
        ];

        $_SESSION['services'] = $this->services;

        parent::__construct($request);
    }

    /**
     * @Route(
     *     "/",
     *     methods={"POST"},
     * )
     *
     * Inserts an item
     *
     * Inserts an item for the requested entity
     *
     * @SWG\Parameter(
     *     name="{entity_field}[]",
     *     in="formData",
     *     type="string",
     *     required=true,
     *     description="Each of the different fields of the entity",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the id of the inserted item",
     *     )
     * )
     * @SWG\Tag(name="insert")
     * @Security(name="Bearer")
     */
    public function insert($entity)
    {
        return $this->handle('upsert', $entity);
    }

    /**
     *
     * @Route(
     *     "/",
     *     methods={"GET"},
     * )
     * @Route(
     *     "/{id}",
     *     methods={"GET"},
     *     requirements={"id"="\d+"}
     * )
     *
     *
     * Returns a [filtered] list of the items of an entity
     *
     * This is a common "get" action. It can be filtered by the following parameters:
     *
     * @SWG\Parameter(
     *     name="search_fields",
     *     in="query",
     *     type="string",
     *     description="<u>Fields of the entity we want to filter by</u> <ul><li><b>Simple</b>: Adds a 'WHERE $fieldname=$value' filter<ul><li><i>search_fields[$fielname]=$value</i></li></ul></li></ul><ul><li><b>Complex</b> : Adds a 'WHERE $fieldname $operand $value' filter<ul><li><i>search_fields[$fielname]['value']=$value&search_fields[$fielname]['operand']=$operand</i> . Operand can be IN, LIKE</li></ul></li></ul>"
     * )
     * @SWG\Parameter(
     *     name="query_filter",
     *     in="query",
     *     type="string",
     *     description="<u>Filters we want to apply to the query</u> <ul><li><b>Sorting</b>: Order the results<ul><li><i>query_filter['sortby'][$i]['field']</i> . Field we want to sort by</li><li><i>query_filter['sortby'][$i]['dir']</i> . Direction we want this field to sort (ASC / DESC)</li></ul></li></ul><ul><li><b>Pagination</b>: Paginates the results<ul><li>query_filters['page_items']=n . n is number of results to retrieve</li><li><i>query_filters['page']=i</i> . i marks the initial index we want to start returning from. If not set, defaults to 1/li><li>Generated limit would be: LIMIT $page_items, $page-1 * $page_items</li></ul>"
     * )
     * @SWG\Parameter(
     *     name="referenced_entities",
     *     in="query",
     *     type="string",
     *     description="<u>Which referenced entities we want to gather</u><br>Example: <i>&referenced_entities=subentity1,subentity1.subsubentity2,entity3</i><br>For each of the items gatherered, it will return a new 'referenced' field with the referenced models<br>Note: it will always return the 'note' reference"
     * )
     * @SWG\Parameter(
     *     name="referenced_entities_contents",
     *     in="query",
     *     type="string",
     *     description="<u>Which referenced entities we want to get all their items</u><br>Example: <i>&referenced_entities_contents=entity1,entity2</i>"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Returns the entity with the ID specified. It's equivalent to '<i>search_fields['id']=$id</i>'. All previous parameters can be applied normally"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of items for the required entity",
     *     )
     * )
     * @SWG\Tag(name="list")
     * @Security(name="Bearer")
     */

    public function get($entity, $id = null)
    {
        return $this->handle(__FUNCTION__, $entity, $id);
    }

    /**
     * @Route(
     *     "/{id}",
     *     methods={"POST"},
     *     requirements={"id"="\d+"}
     * )
     *
     * Updates an item for the requested entity
     *
     * @SWG\Parameter(
     *     name="{entity_field}[]",
     *     in="formData",
     *     type="string",
     *     required=true,
     *     description="Each of the different fields of the entity",
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="ID of the item to update"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the id of the updated item",
     *     )
     * )
     * @SWG\Tag(name="update")
     * @Security(name="Bearer")
     */
    public function update($entity, $id = null)
    {
        return $this->handle('upsert', $entity, $id);
    }

    /**
     * @Route(
     *     "/{id}",
     *     methods={"DELETE"},
     *     requirements={"id"="\d+"}
     * )
     *
     * Deletes an item from an entity
     *
     * No furter explanation, delete from entity where id=$id
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="ID of the item to delete"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the id of the deleted item",
     *     )
     * )
     * @SWG\Tag(name="delete")
     * @Security(name="Bearer")
     */
    public function delete($entity, $id = null)
    {
        return $this->handle(__FUNCTION__, $entity, $id);
    }

    /**
     * @Route(
     *     "/schema",
     *     methods={"GET"}
     * )
     */
    public function getSchema($entity, $id = null)
    {
        return $this->handle(__FUNCTION__, $entity, $id);
    }

    /**
     * @Route(
     *     "/getMetadata",
     *     methods={"GET"}
     * )
     */
    public function getMetadata($entity, $id = null)
    {
        return $this->handle(__FUNCTION__, $entity, $id);
    }


    private function handle($action, $entity, $id = null)
    {
        #we make it like this to reuse the magic __call parent method that can be used with Laravel
        $uri_params[] = $entity;
        $uri_params[] = $id;
        return $this->generateResponse(parent::__call($action, $uri_params), $action);
    }

    public function generateResponse($data, $action = null)
    {

        #assigned to have it on the destruct()
        $this->response = $data;
        // All actions except 'get' do not return 'items schema' => Generate it!!
        if($action != 'get'){
            return JsonResponse::fromJsonString(json_encode($this->prepareResponseByItems($data)));
        } else {
            return JsonResponse::fromJsonString(json_encode($data));
        }
    }

}