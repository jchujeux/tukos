<?php

namespace TukosLib\Objects\Actions\Overview;

use TukosLib\Objects\Actions\AbstractAction;
use TukosLib\Objects\Views\Overview\Models\Get as OverviewGetModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class GridSearch extends AbstractAction{
    function __construct($controller){
        parent::__construct($controller);
        $this->actionModel  = new OverviewGetModel($this);
    }
    function response($query){
        if (!empty($pattern = Utl::extractItem('pattern', $query['storeatts']['where']))){
            $query['storeatts']['where'][] = [SUtl::longFilter('name', ['RLIKE', $pattern]), SUtl::longFilter('comments', ['RLIKE', $pattern]), 'or' => true];
        }
    	$result = $this->actionModel->getOverviewGrid($query);
        //Feedback::reset();// or else the feedback is added to the JsonRest response and screws-up the dgrid
        Feedback::add('done');
        return $result;
    }
}
?>
