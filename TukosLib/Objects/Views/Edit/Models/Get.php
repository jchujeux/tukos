<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Get extends ViewsGetModel {

    public function respond(&$response, $query, $cols=['*']){
        if (!empty($query['params'])){
            $this->modelGetOne  = (empty($query['params']['getOne'])  ? $this->modelGetOne : $query['params']['getOne']);
            $this->modelGetAll  = (empty($query['params']['getAll'])  ? $this->modelGetAll : $query['params']['getAll']);
        }
        $this->getData($response, $query, $cols);
    }

    protected function setProtection(&$data){
        if (empty($data['value']['id']) || $this->user->hasUpdateRights($data['value'])){
        	foreach ($this->view->dataWidgets as $col => $description){
        		$data['disabled'][$col] = (isset($description['atts']['edit']['disabled']) &&  $description['atts']['edit']['disabled']) ? true : false;
        	}

            foreach ($this->view->dataWidgets as $col => $description){
        		$data['readOnly'][$col] = (isset($description['atts']['edit']['readOnly']) &&  $description['atts']['edit']['readOnly']) ? true : false;
            }

        	foreach ($this->view->subObjects as $col => $description){
        		$data['disabled'][$col] = (isset($description['atts']['disabled']) &&  $description['atts']['disabled']) ? true : false;
        	}
        	$data['disabled']['save'] = false;
        	$data['disabled']['delete'] = false;
        	$data['disabled']['process'] = false;
        	return false;
        }else{
        	foreach ($this->view->dataWidgets as $col => $description){
        		$data['readOnly'][$col] = true;
        	}
        	foreach ($this->view->subObjects as $col => $description){
        		$data['disabled'][$col] = true;
        	}
        	$data['disabled']['save'] = true;
        	$data['disabled']['delete'] = true;
        	$data['disabled']['process'] = true;
        	return true;
        }
    }

    protected function unHiddenEditCols($query, $cols){
        if ($cols === ['*']){
            $cols = $this->view->allowedGetCols();
        }
        $unHiddenCols = [];
        $customElements = $this->getElementsCustomization($query);
        foreach ($cols as $col){
            if ((!isset($customElements[$col]['atts']['hidden']) && empty($this->view->dataWidgets[$col]['atts']['edit']['hidden'])) ||
                empty($customElements[$col]['atts']['hidden']) || in_array($col, $this->view->model->extendedNameCols)){
                $unHiddenCols[] = $col;
            }
        }
        return $unHiddenCols;
    }

    protected function getData(&$response, $query, $cols=['*']){
        $contextPathId = (isset($query['contextpathid']) ?  Utl::extractItem('contextpathid', $query) : $this->user->getContextId($this->model->objectName));
        $response['data']['disabled'] = [];//hack so that it is the first element of $response['data']
        $dupId = null;
        if (empty($query)){
            $value = $this->initialize('objToEdit');
            $customMode = 'object';
            $feedback = $this->view->tr('donenewobject');
        }else if(isset($query['dupid'])){
            $dupId = $query['dupid'];
            $value = $this->duplicate($dupId, 'objToEdit');
            if (isset($query['grade'])){
                //$response['data']['value']['grade'] = $query['grade'];
                $value['grade'] = $query['grade'];
            }
            $response['forceMarkIfChanged'] = true;
            $customMode = 'object';
            Feedback::add([$this->view->tr('doneduplicateditem') => $dupId]);
        }else{
            $where = isset($query['storeatts']['where']) ? $query['storeatts']['where'] : $query;
            $value = $this->getOne($where, $this->unHiddenEditCols($where, $cols), 'objToEdit');
            if (isset($value['id'])){
            	$customMode = 'item';
            }else{
            	$value = $this->initialize('objToEdit', isset($query['storeatts']['init']) ? $query['storeatts']['init'] : []);
            	$customMode = 'object';
            }
        }
    	$response['data']['value'] = $value;

        if (!empty($this->view->subObjects)){
            SubObjectsGet::getData($response, $this, $contextPathId);
        }

        $response['readOnly'] = $this->setProtection($response['data']);
        if ($dupId){
        	if (!empty($itemCustomization = $this->view->model->getItemCustomization(['id' => $dupId], ['edit', $this->paneMode]))){
        		$response['itemCustomization'] = $itemCustomization;
        	}
        }
        $response = $this->mergeCustomization($response, $customMode, Utl::getItem('id', $response['data']['value']));
    }
    
    protected function mergeCustomization($response, $customMode, $itemId = null){
    	return Utl::array_merge_recursive_replace(
    		$response, 
    		$customMode === 'object'
    				? $this->user->getCustomView($this->objectName, 'edit', $this->paneMode, [])
    				: $this->model->getCombinedCustomization(['id' => $itemId], 'edit', $this->paneMode, [])
    	);
    }
}
?>
