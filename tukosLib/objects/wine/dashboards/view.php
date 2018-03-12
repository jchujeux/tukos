<?php
/**
 *
 * class for viewing methods and properties for the wine application dashboard
 */
namespace TukosLib\Objects\Wine\Dashboards;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk; 
 
class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Cellar', 'Description');
        $tableAtts = ['maxHeight' => '300px', 'minWidth' => '160px', 'columns' => ['qty' => ['label' => $this->tr('qty'), 'field' => 'qty', 'width' => 100]]];
        
        $customDataWidgets = [
            'inventorydate' => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('Dashboard date'), 'style' => ['width' => '10em']]]],
            'quantity'   => ViewUtils::textBox($this, 'Quantity', ['atts' => ['edit' => ['style' => ['width' => '6em']]]]),
            'count'      => ViewUtils::textBox($this, 'Count'   , ['atts' => ['edit' => ['style' => ['width' => '6em']]]]),
/*
        	'quantityperregion'   => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => $this->tr('Quantities per region')  , 'dictionary' => ['y' => 'qty', 'text' => 'region']]]],
            'quantitypercategory' => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => $this->tr('Quantities per category'), 'dictionary' => ['y' => 'qty', 'text' => 'category']]]], 
            'quantitypercolor'    => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => $this->tr('Quantities per color')   , 'dictionary' => ['y' => 'qty', 'text' => 'color']]]],
            'quantitypersugar'    => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => $this->tr('Quantities per sugar')   , 'dictionary' => ['y' => 'qty', 'text' => 'sugar']]]],
*/
        		
        	'quantityperregion'   => ['type' => 'pieChart', 'atts' => ['edit' => ['title' => $this->tr('Quantities per region')  , 'showTable' => 'yes', 'tableAtts' => $this->tableAtts('region'), 'series' => ['thePlot' => ['value' => ['y' => 'qty', 'text' => 'region']]]]]],
            'quantitypercategory' => ['type' => 'pieChart', 'atts' => ['edit' => ['title' => $this->tr('Quantities per category'), 'showTable' => 'yes', 'tableAtts' => $this->tableAtts('category'), 'series' => ['thePlot' => ['value' => ['y' => 'qty', 'text' => 'category']]]]]],
            'quantitypercolor'    => ['type' => 'pieChart', 'atts' => ['edit' => ['title' => $this->tr('Quantities per color')   , 'showTable' => 'yes', 'tableAtts' => $this->tableAtts('color'), 'series' => ['thePlot' => ['value' => ['y' => 'qty', 'text' => 'color']]]]]],
            'quantitypersugar'    => ['type' => 'pieChart', 'atts' => ['edit' => ['title' => $this->tr('Quantities per sugar')   , 'showTable' => 'yes', 'tableAtts' => $this->tableAtts('sugar'), 'series' => ['thePlot' => ['value' => ['y' => 'qty', 'text' => 'sugar']]]]]],
        	'quantitypervintage'  => [
                'type' => 'columnsChart',  
                'atts' => ['edit' => [
                	'title' => $this->tr('Quantities per vintage'), 'chartStyle' => ['width' => '1200px'], 'kwArgs' => ['sort'=> [['attribute' => 'vintage', 'descending' => 'true']]], 
                	'series' => ['thePlot' => ['value' => ['y' => 'qty', 'text' => 'vintage']]], 'axes' => ['x' => ['labelCol' => 'vintage']]
                ]]
        	],
/*
        'countperregion' => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => 'Count per region'  , 'dictionary' => ['y' => 'count', 'text' => 'region']]]],
      'countpercategory' => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => 'Count per category', 'dictionary' => ['y' => 'count', 'text' => 'category']]]],
         'countpercolor' => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => 'Count per color'   , 'dictionary' => ['y' => 'count', 'text' => 'color']]]],
         'countpersugar' => ['type' => 'pieChart'    ,  'atts' => ['edit' =>  ['title' => 'Count per sugar'   , 'dictionary' => ['y' => 'count', 'text' => 'sugar']]]],
       'countpervintage' => ['type' => 'ColumnsChart',  'atts' => ['edit' =>  ['title' => 'Count per vintage', 'dictionary' => ['y' => 'count', 'text' => 'vintage']],
                                                                    'kwArgs' => ['sort'=> [['attribute' => 'vintage', 'descending' => 'true']]]]],
*/
        ];
        
        $absentWidgets = [
            'countperregion', 'countpercategory', 'countpercolor', 'countpercolor', 'countpersugar', 'countpervintage', 'countpercategoryperregion', 'quantitypercategoryperregion',
            'countpercolorperregion', 'quantitypercolorperregion', 'countpersugarperregion', 'quantitypersugarperregion', 'countpervintageperregion', 'quantitypervintageperregion'
        ];
        $this->customize($customDataWidgets, [], ['edit' => $absentWidgets, 'grid' => $absentWidgets, 'get' => $absentWidgets, 'post' => $absentWidgets]);
    }
    
    function tableAtts($description){
    	return ['maxHeight' => '300px', 'minWidth' => '160px', 'columns' => [$description => ['label' => $this->tr($description), 'field' => $description, 'width' => 100], 'qty' => ['label' => $this->tr('qty'), 'field' => 'qty', 'width' => 50]]];
    }


}
?>
