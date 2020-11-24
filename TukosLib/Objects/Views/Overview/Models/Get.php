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
        $summaryStoreAtts = [/*'where' => $query['storeatts']['where'], */'eliminateditems' => Utl::getItem('eliminateditems', $query['storeatts'])];
        $result = $this->getGrid($query['storeatts'], $this->unHiddenOverviewGridCols($this->view->gridCols()), false, 'objToOverview');
        $result['summary'] = ['value' => $this->model->summary($summaryStoreAtts)];
        return $result;
    }
    
}
?>
