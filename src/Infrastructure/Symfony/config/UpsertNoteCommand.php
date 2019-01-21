<?php

namespace App\Application\Commands\Note;
use Osds\Api\Application\Commands\BaseCommand;
use Osds\Api\Application\Commands\UpsertModelCommand;

class UpsertNoteCommand extends UpsertModelCommand {

    public function execute() {

        foreach($this->args as $key => $value)
        {
            if($key != 'user_id' && preg_match('/([a-z]*)_id/', $key, $res))
            {
                $this->args['related_model'] = $res[1];
                $this->args['related_model_id'] = $value;
                unset($this->args[$key]);
            }
        }
        return parent::execute();

    }


}