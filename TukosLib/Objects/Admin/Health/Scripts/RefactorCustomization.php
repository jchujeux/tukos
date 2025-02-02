<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;


use TukosLib\TukosFramework as Tfk;

class RefactorCustomization {

    function __construct($parameters){ 
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $customViewsModel = $objectsStore->objectModel('customviews');
            $customizationsToRefactor = $customViewsModel->getAll(['cols' => ['id', 'customization'], 'where' => [
                ['col' => 'customization', 'opr' => 'IS NOT NULL', 'values' => null], ['col' => 'customization', 'opr' => 'RLIKE', 'values' => 'editConfig']
            ]], ['customization' => []]);
            $numberOfCustomViewsRefactored = 0;
            array_walk($customizationsToRefactor, function($item) use ($customViewsModel, $numberOfCustomViewsRefactored) {
                $editConfigRefactored = false;
                $chartIds = false;
                if (!empty ($oldCharts = Utl::drillDown($item['customization'], ['editConfig', 'charts'])) && is_string($oldCharts)){
                    $oldCharts = json_decode($oldCharts, true);
                    if (array_is_list($oldCharts)){
                        $newCharts = Utl::toAssociative($oldCharts, 'id');
                        $chartIds = array_keys($newCharts);
                        foreach($newCharts as &$row){
                            unset($row['idg']);
                        }
                        $item['customization'] = ['editConfig' => ['charts' => $newCharts]];
                        $editConfigRefactored = true;
                    }
                }else if(!empty($oldCharts)){
                    $chartIds = array_keys($oldCharts);
                }
                $chartsDescriptionsRefactored = false;
                if ($chartIds !== false && !empty ($widgetsDescription = Utl::getItem('widgetsDescription', $item['customization']))){
                    foreach($widgetsDescription as $name => $description){
                        if (str_starts_with($name, 'chart') && ($id = substr($name, 5)) > 0){
                            if (in_array($id, $chartIds)){
                                if ($atts = Utl::getItem('atts', $description)){
                                    foreach(['axesToInclude', 'plotsToInclude', 'kpisToInclude', 'itemsSetsToInclude'] as $descriptionName){
                                        if (!empty($oldDescription = Utl::getItem($descriptionName, $atts)) && is_string($oldDescription)){
                                            $oldDescription = json_decode($oldDescription, true);
                                            $id = 1;
                                            foreach($oldDescription as &$row){
                                                unset($row['idg']);
                                                $row['id'] = $id;
                                                $id += 1;
                                            }
                                            $item['customization']['widgetsDescription'][$name]['atts'][$descriptionName] = Utl::toAssociative($oldDescription, 'id');
                                        }
                                    }
                                }
                            }else{
                                $item['customization'][$name] = '~delete';
                            }
                            $chartsDescriptionsRefactored = true;
                        }
                    }
                }
                if ($editConfigRefactored || $chartsDescriptionsRefactored){
                    $numberOfCustomViewsRefactored +=1;
                    $customViewsModel->updateOne($item);
                }
            });
            echo 'done - number of customviews items refactored: ' . $numberOfCustomViewsRefactored;
            $customizationsToRefactor = SUtl::$tukosModel->getAll(['cols' => ['id', 'object', 'custom'], 'where' => [
                ['col' => 'custom', 'opr' => 'IS NOT NULL', 'values' => null], [['col' => 'custom', 'opr' => 'RLIKE', 'values' => 'editConfig'], ['col' => 'custom', 'opr' => 'RLIKE', 'values' => '"chart[0-9]*"', 'or' => true]]
            ]]);
            $numberOfItemsCustomRefactored = 0;
            array_walk($customizationsToRefactor, function($item) use (&$numberOfItemsCustomRefactored, $objectsStore){
                $editConfigRefactored = false;
                $oldCustom = json_decode($item['custom'], true);
                if (!empty ($oldCharts = Utl::drillDown($oldCustom, ['edit', 'tab', 'editConfig', 'charts'])) && is_string($oldCharts)){
                    $oldCharts = json_decode($oldCharts, true);
                    if (array_is_list($oldCharts)){
                        $newCharts = Utl::toAssociative($oldCharts, 'id');
                        foreach($newCharts as &$row){
                            unset($row['idg']);
                        }
                        $item['custom'] = Utl::array_merge_recursive_replace($oldCustom, ['edit' => ['tab' => ['editConfig' => ['charts' => $newCharts]]]]);
                        $objectsStore->objectModel($item['object'])->updateOne($item);
                        $editConfigRefactored = true;
                    }
                }
                $chartsDescriptionRefactored = false;
                if (!empty ($widgetsDescription = Utl::drillDown($oldCustom, ['edit', 'tab', 'widgetsDescription']))){
                    $newWidgetsDescription = [];
                    foreach($widgetsDescription as $name => $description){
                        if (str_starts_with($name, 'chart') && ($id = substr($name, 5)) > 0){
                            $chartIds = array_keys($objectsStore->objectModel($item['object'])->getCombinedCustomization(['id' => $item['id']], 'edit', 'tab', ['editConfig', 'charts']));
                            if (in_array($id, $chartIds)){
                                if ($atts = Utl::getItem('atts', $description)){
                                    $newDescriptions = [];
                                    foreach(['axesToInclude', 'plotsToInclude', 'kpisToInclude', 'itemsSetsToInclude'] as $descriptionName){
                                        if (!empty($oldDescription = Utl::getItem($descriptionName, $atts)) && is_string($oldDescription)){
                                            $oldDescription = json_decode($oldDescription, true);
                                            $id = 1;
                                            foreach($oldDescription as &$row){
                                                unset($row['idg']);
                                                $row['id'] = $id;
                                                $id += 1;
                                            }
                                            $newDescriptions[$descriptionName] = Utl::toAssociative($oldDescription, 'id');
                                        }
                                    }
                                    if (!empty($newDescriptions)){
                                        $newWidgetsDescription[$name] = ['atts' => $newDescriptions];
                                    }
                                }
                            }else{
                                $newWidgetsDescription[$name] = '~delete';
                            }
                        }
                    }
                    if (!empty($newWidgetsDescription)){
                        $item['custom'] = Utl::array_merge_recursive_replace($oldCustom, ['edit' => ['tab' => ['widgetsDescription' => $newWidgetsDescription]]], true, false, '~replace', '~xxx');
                        $objectsStore->objectModel($item['object'])->updateOne($item);
                        $chartsDescriptionRefactored = true;
                    }
                }
                if ($editConfigRefactored || $chartsDescriptionRefactored){
                    $numberOfItemsCustomRefactored +=1;
                }
            });
            echo '<br>done - number of items customization refactored: ' . $numberOfItemsCustomRefactored;

            $customWidgetsModel = $objectsStore->objectModel('customwidgets');
            $customizationsToRefactor = $customWidgetsModel->getAll(['cols' => ['id', 'customization'], 'where' => [
                ['col' => 'customization', 'opr' => 'IS NOT NULL', 'values' => null]
            ]], ['customization' => []]);
            $numberOfCustomWidgetsRefactored = 0;
            array_walk($customizationsToRefactor, function($item) use ($customWidgetsModel, $numberOfCustomWidgetsRefactored) {
                $widgetsCustomizationRefactored = false;
                $customization = $item['customization'];
                foreach(['axesToInclude', 'plotsToInclude', 'kpisToInclude', 'itemsSetsToInclude'] as $descriptionName){
                    if (!empty($oldDescription = Utl::getItem($descriptionName, $customization)) && is_string($oldDescription)){
                        $oldDescription = json_decode($oldDescription, true);
                        $id = 1;
                        foreach($oldDescription as &$row){
                            unset($row['idg']);
                            $row['id'] = $id;
                            $id += 1;
                        }
                        $item['customization'][$descriptionName] = Utl::toAssociative($oldDescription, 'id');
                        $widgetsCustomizationRefactored = true;
                    }
                }
                if ($widgetsCustomizationRefactored){
                    unset($item['customization']['title']);
                    $numberOfCustomWidgetsRefactored +=1;
                    $customWidgetsModel->updateOne($item);
                }
            });
                echo '<br>done - number of customwidgets items refactored: ' . $numberOfCustomWidgetsRefactored;
                
                
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in RefactorCustomizaton: ', $e->getUsageMessage());
        }
    }
}
?>
