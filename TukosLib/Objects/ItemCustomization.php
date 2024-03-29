<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\Objects\StoreUtilities as SUtl;

use TukosLib\TukosFramework as Tfk;

trait ItemCustomization {

    public function getItemCustomization($where, $viewPaneMode, $keys = []){
        if (in_array('custom', $this->allCols)){
            $itemCustomization = $this->getOne(['where' => $where, 'cols' => ['custom']], ['custom' => $viewPaneMode]);
            if (empty($itemCustomization['custom']) && $viewPaneMode === ['edit', 'mobile']){
                $itemCustomization = $this->getOne(['where' => $where, 'cols' => ['custom']], ['custom' => ['edit', 'tab']]);
            }
            if (!empty($itemCustomization['custom'])){
                $itemCustom = Utl::drillDown($itemCustomization['custom'], $keys, []);
                if (!empty($itemCustomization['custom']['itemcustomviewid'])){
                    $itemCustomViewId  = $itemCustomization['custom']['itemcustomviewid'];//Utl::extractItem('itemcustomviewid', $itemCustomization);
                    SUtl::addIdCol($itemCustomViewId);
                    $customViewItem = Tfk::$registry->get('objectsStore')->objectModel('customviews')->getOne(['where' => ['id' => $itemCustomViewId], 'cols' => ['customization']], ['customization' => $keys], [], null);
                    if (empty($customViewItem)){
                        Feedback::add([$this->tr('customviewnotfound') => $itemCustomViewId]);
                        return $itemCustomization;
                    }else{
                        return Utl::array_merge_recursive_replace($customViewItem['customization'], $itemCustom);
                    }
                }else{
                    return $itemCustom;
                }
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

    public function deleteItemCustomization($where, $valuesToDelete){
        $item =  $this->getOne(['where' => $where, 'cols' => ['custom']], ['custom' => []]);
        if (empty($item['custom'])){
            Feedback::add('nocustomizationtodelete');
        }else{
            $customization = $item['custom'];
            Utl::drillDownDelete($customization, $valuesToDelete);
            $this->updateOne(['custom' => json_encode($customization)], ['where' => $where]);
            unset($this->itemCustomizationCache);
            return $customization;
        }
    }

    public function getCombinedCustomization($where, $view, $paneMode, $keys){ 
        $paneMode = empty($paneMode) ? 'tab' : strtolower($paneMode);
        return Utl::array_merge_recursive_replace($this->user->getCustomView($this->objectName, $view, $paneMode, $keys),   $this->getItemCustomization($where, [strtolower($view), $paneMode], $keys));
    }
}
?>
