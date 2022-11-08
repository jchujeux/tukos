<?php

namespace TukosLib\Objects\Views\Edit\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Objects\Views\Edit\Models\SubObjectsGet;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;

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
        		$data['readonly'][$col] = (isset($description['atts']['edit']['readonly']) &&  $description['atts']['edit']['readonly']) ? true : false;
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
        		$data['readonly'][$col] = true;
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
        return $cols;
/*
        $unHiddenCols = [];

        $customElements = $this->getElementsCustomization($query);
        foreach ($cols as $col){
            if ((!isset($customElements[$col]['atts']['hidden']) && empty($this->view->dataWidgets[$col]['atts']['edit']['hidden'])) ||
                empty($customElements[$col]['atts']['hidden']) || in_array($col, $this->view->model->extendedNameCols)){
                $unHiddenCols[] = $col;
            }
        }
        return $unHiddenCols;
*/
    }

    protected function getData(&$response, $query, $cols=['*']){
        $contextPathId = (isset($query['contextpathid']) ?  Utl::extractItem('contextpathid', $query) : $this->user->getContextId($this->model->objectName));
        $response['data']['disabled'] = [];//hack so that it is the first element of $response['data']
        $dupId = null;
        if (empty($query)){
            $value = $this->initialize('objToEdit');
            $customMode = 'object';
            $allowCustomValue = true;
        }else if(isset($query['dupid'])){
            $dupId = $query['dupid'];
            $value = $this->duplicate($dupId, 'objToEdit');
            if (isset($query['grade'])){
                //$response['data']['value']['grade'] = $query['grade'];
                $value['grade'] = $query['grade'];
            }
            $response['forceMarkIfChanged'] = true;
            $customMode = 'item';
            $allowCustomValue = false;
            $value['id'] = $dupId;
            Feedback::add([$this->view->tr('doneduplicateditem') => $dupId]);
        }else{
            $storeAtts = Utl::extractItem('storeatts', $query, []);
            $where = Utl::getItem('where', $storeAtts, $query);
            $cols = Utl::getItem('cols', $storeAtts, $cols);
            $value = $this->getOne($where, $this->unHiddenEditCols($where, $cols), 'objToEdit', true);
            if (isset($value['id'])){
            	$customMode = 'item';
            	$allowCustomValue = false;
            }else{
            	$value = $this->initialize('objToEdit', isset($storeAtts['init']) ? $storeAtts['init'] : []);
            	$customMode = 'object';
            	$allowCustomValue = true;
            }
        }
    	$response['data']['value'] = $value;

        if (!empty($this->view->subObjects)){
            SubObjectsGet::getData($response, $this, $contextPathId);
        }

        $response['readonly'] = $this->setProtection($response['data']);
        if ($dupId){
        	if (!empty($itemCustomization = $this->view->model->getItemCustomization(['id' => $dupId], ['edit', strtolower($this->paneMode)]))){
        		$response['itemCustomization'] = $itemCustomization;
        	}
        }
        if (method_exists($this->view, 'preMergeCustomizationAction')){
            $response = $this->view->preMergeCustomizationAction($response, $customMode);
        }
        $response = $this->mergeCustomization($response, $customMode, $allowCustomValue);
        if ($dupId){
            unset($response['data']['value']['id']);
        }
    }
    
    protected function mergeCustomization($response, $customMode, $allowCustomValue){
        if (!$allowCustomValue){
            $value = $response['data']['value'];
        }
        $response = Utl::array_merge_recursive_replace(
    		$response, 
    		$customMode === 'object'
    				? $this->user->getCustomView($this->objectName, 'edit', $this->paneMode, [])
    				: $this->model->getCombinedCustomization(['id' => Utl::getItem('id', $value)], 'edit', $this->paneMode, [])
    	);
        if (!$allowCustomValue){
            $response['data']['value'] = $value;
        }
        return $response;
    }
}
?>
