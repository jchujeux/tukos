<?php

namespace TukosLib\Objects\Actions\Edit;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Process extends AbstractAction{

    function response($query){/* default behavior is nosave, process and get*/
        if (empty($query['params']['save'])){
            $okToProcess = true;
        }else{
            $this->saveViewModel    = $this->objectsStore->objectViewModel($this->controller, 'Edit', 'Save');
            $saveStatus = $this->saveViewModel->save($query, /*ignore no change*/true);
            if ($saveStatus === null){//no change
            	$okToProcess = true;
            }else if ($saveStatus === false){// something went wrong
            	$okToProcess = false;
            }else{
                $query['id'] = $saveStatus;
                $okToProcess = true;
            }
        }
        if ($okToProcess){
            $response = [];
            if (empty($query['params']['noprocess'])){
                $this->processViewModel = $this->objectsStore->objectViewModel($this->controller, 'Edit', 'Process');
                $response = $this->processViewModel->process($query);
            }
            if (empty($query['params']['noget'])){
                $this->getViewModel     = $this->objectsStore->objectViewModel($this->controller, 'Edit', 'Get');
                $response = [];
                $this->getViewModel->respond($response, ['id' => $query['id']]);
                $response['title'] = $this->view->tabEditTitle($response['data']['value']);
                return $response;
            }else{
                return $response;
            }
        }else{
            return [];
        }
    }
}
?>
