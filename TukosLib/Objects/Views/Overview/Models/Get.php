<?php

namespace TukosLib\Objects\Views\Overview\Models;

use TukosLib\Objects\Views\Models\Get as ViewsGetModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Get extends ViewsGetModel {
    protected function unHiddenOverviewGridCols($cols){
        $customElements = $this->getElementsCustomization();
        $unHiddenCols = [];
        foreach ($cols as $col){
            if (!isset($customElements['overview']['atts']['columns'][$col]['hidden']) || !$customElements['overview']['atts']['columns'][$col]['hidden']){
                $unHiddenCols[] = $col;
            }
        }
        return $unHiddenCols;
    }
    function getOverviewGrid($query){
        $storeAtts = $query['storeatts'];
        $storeAtts['where'] = array_merge($this->user->getCustomView($this->objectName, 'overview', $this->paneMode, ['data', 'filters', 'overview']), $storeAtts['where']);
        $result = $this->getGrid($storeAtts, $this->unHiddenOverviewGridCols($this->view->gridCols()), false, 'objToOverview');
        $result['summary'] = ['value' => $this->model->summary()];
        return $result;
    }
    
}
?>
