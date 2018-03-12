<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\Objects\StoreUtilities as SUtl;

use TukosLib\TukosFramework as Tfk;

trait ItemCustomization {

    public function getItemCustomization($where, $keys){
        if (in_array('custom', $this->allCols)){
            $itemCustomization = $this->getOne(['where' => $where, 'cols' => ['custom']], ['custom' => $keys]);
            if (!empty($itemCustomization['custom'])){
                $itemCustomization = $itemCustomization['custom'];
                if (!empty($itemCustomization['itemcustomviewid'])){
                    $itemCustomViewId  = $itemCustomization['itemcustomviewid'];//Utl::extractItem('itemcustomviewid', $itemCustomization);
                    SUtl::addIdCol($itemCustomViewId);
                    $view = array_shift($keys);
                    $customViewItem = Tfk::$registry->get('objectsStore')->objectModel('customviews')->getOne(['where' => ['id' => $itemCustomViewId], 'cols' => ['customization']], ['customization' => $keys], [], null);
                    if (empty($customViewItem)){
                        Feedback::add([$this->tr('customviewnotfound') => $itemCustomViewId]);
                        return $itemCustomization;
                    }else{
                        return Utl::array_merge_recursive_replace($customViewItem['customization'], $itemCustomization);
                    }
                }else{
                    return $itemCustomization;
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

    public function getCombinedCustomization($where, $view, $keys){ 
        return Utl::array_merge_recursive_replace($this->user->getCustomView($this->objectName, $view, $keys),   $this->getItemCustomization($where, array_merge([$view], $keys)));
    }
}
?>
