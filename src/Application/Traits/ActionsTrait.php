<?php

namespace Osds\Backoffice\Application\Traits;

use Osds\Backoffice\Infrastructure\Controllers\LoginController;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Trait ActionsTrait
 *
 * Has all the actions that can be performed (create, list, view, delete...)
 *
 * @package Osds\Backoffice\Classes
 */

trait ActionsTrait
{

    /**
     * Loads an empty form to create a new item
     *
     * @return mixed
     */
    public function loadEmptyForm($model)
    {
        #we need them in order to get mandatory references (foreign relations)
        $data['twig_vars'] = $this->getReferencedContents([], $model);
        return $this->generateView($data, 'create');
    }

    /**
     * Creates an item from the received data and redirects to the view or the list, if it fails
     *
     * @return bool
     */
    public function create($model)
    {
        try
        {
            $this->preTreatBeforeSaving($model);

            $result = $this->performAction('create');
            if (isset($result['items'][0]['upsert_id'])) {
                return $this->redirect("/{$model}/edit/{$result['items'][0]['upsert_id']}", "success", "create_ok");
            } else {
                return $this->redirect("/{$model}", "warning", $result['error_message']);
            }

        } catch(\Exception $e)
        {
            $this->redirect("/{$model}", "danger", "create_ko", $e);
        }

        return true;

    }

    /**
     * Lists an entity items
     *
     * @Route(
     *     "/{entity}",
     *     methods={"GET"}
     * )
     *
     * @param null $entity
     * @return mixed
     */
    public function list($entity = null)
    {

        $this->entity = 'customer';

        #pagination
        $this->request_data['get']['get_referenced'] = true;
        $this->request_data['get']['query_filters']['page_items'] = $this->config['domain_structure']['pagination']['items_per_page'];
        $data = $this->performAction('list');

        $data = $this->preTreatDataBeforeDisplaying($entity, $data);

        return $this->generateView($data);

    }

    /**
     *
     * Detailed view of an item
     *
     * @param $model
     * @param $id => ID of model to view
     * @return mixed
     * @internal param $prefix => first param of url (model)
     */
    public function detail($model, $id)
    {
        #we need to get the referenced contents in order to list them on the item form (to be able to list them)

        #add the ID of the item as a filter to the API
        $this->request_data['uri'][] = $id;
        $this->request_data['get']['get_referenced'] = true;
        #gather fields that are other models contents
        if (isset($this->models[$model]['fields']['fillable'])) {
            foreach ($this->models[$model]['fields']['fillable'] as $fillable_field) {
                if (strstr($fillable_field, '.')) {
                    list($required_model, $required_field) = explode('.', $fillable_field);
                    $this->request_data['get']['get_models_contents'][] = $required_model;
                }
            }
        }
        $data = $this->performAction('list');
        $data = $this->preTreatDataBeforeDisplaying($model, $data);

        unset($this->request_data['uri']);

//        $referenced_contents['twig_vars'] = $this->getReferencedContents($data['schema'], $model);
//        $data = array_merge($data, $referenced_contents);

        return $this->generateView($data);
    }

    /**
     * Updates a model
     *
     * @param $model
     * @return mixed
     */
    public function update($model, $id)
    {
        try
        {
            $this->preTreatBeforeSaving($model);

            $this->request_data['uri'][] = $id;
            $result = $this->performAction('update');
            #redirect to detail
            if (isset($result['items'][0]['upsert_id'])) {
                return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "success", "edit_ok");
            } else {
                return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "danger", "edit_ko", $result['items'][0]['error_message']);
            }
        } catch(\Exception $e)
        {
            return $this->redirect("/{$model}/edit/{$this->request_data['uri'][0]}", "danger", "edit_ko", $e);
        }

    }

    public function delete($model, $id)
    {
//        $model = \Route::current()->parameter('prefix');
        try
        {
            $this->request_data['uri'][] = $id;
            $result = $this->performAction('delete');
            #redirect to detail
            if (isset($result['items'][0]['deleted_id'])) {
                return $this->redirect("/{$model}", "success", "delete_ok");
            } else {
                return $this->redirect("/{$model}", "danger", "delete_ko", $result['items'][0]['error_message']);
            }
        } catch(\Exception $e)
        {
            return $this->redirect("/{$model}", "danger", "delete_ko", $e);
        }

    }


    /**
     *
     * Get all the contents of the referenced Models (foreign-keyed models on DB)
     *
     * @return array
     */
    private function getReferencedContents($schema_info = null, $model = null)
    {
        if($schema_info == null)
        {
            $schema_info_request = $this->performAction('getSchema');
            $schema_info = $schema_info_request['items'][0]['fields'];
        }

        $referenced_contents = [];
        
        return $referenced_contents;
    }

    private function preTreatBeforeSaving($model)
    {
        #treat field before saving it
        if(isset($this->config['domain_structure']['models'][$model]['fields']['fields_schema']))
        {
            foreach($this->config['domain_structure']['models'][$model]['fields']['fields_schema'] as $field => $field_schema)
            {
                #this field has callbacks, call them
                if(isset($this->request_data['post'][$field]) && isset($field_schema['callbacks']))
                {
                    $field_value = $this->request_data['post'][$field];
                    foreach($field_schema['callbacks'] as $callback)
                    {
                        if($this->isMultilanguageField($field_value))
                        {
                            foreach($field_value as $lang => $value)
                            {
                                $field_value[$lang] = $this->{$callback}($field_value[$lang]);
                            }
                        } else {
                            $field_value = $this->{$callback}($field_value);
                        }
                    }
                    $this->request_data['post'][$field] = $field_value;
                }
            }
        }


        #if model has user_id field, fill it with session_id
        if(
            isset($this->config['domain_structure']['models'][$model]['schema']['by_user'])
            && $this->config['domain_structure']['models'][$model]['schema']['by_user'] == true
        )
        {
            $session_data = $this->session->get(LoginController::var_session_name);
            $this->request_data['post']['user_id'] = $session_data['id'];
        }

        #if it's multilanguage, json encode its values
        if(isset($this->config['domain_structure']['languages']))
        {
            foreach($this->request_data['post'] as $field => $value)
            {
                if($this->isMultilanguageField($value))
                {
                    $this->request_data['post'][$field] = json_encode($value);
                }
            }
        }

        #set to null empty values to avoid casting errors with the db
        foreach($this->request_data['post'] as $field => &$value)
        {
            if($value == '') $value = 'DB_NULL';
        }
    }

}