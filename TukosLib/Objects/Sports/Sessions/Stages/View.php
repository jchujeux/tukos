<?php
namespace TukosLib\Objects\Sports\Sessions\Stages;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Sports\Sessions\TemplatesViewMixin;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    use TemplatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = array_merge([
	            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '20em;']]],],
	            'duration'          =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration'),
	            'stagetype'     => ViewUtils::storeSelect('stagetype', $this, 'Stage'),
                'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Intensity', [true, 'ucfirst', true]),
                'stress'        => ViewUtils::storeSelect('stress', $this, 'Mechanical stress', [true, 'ucfirst', true]),
	            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport'),
	            'summary'    => ViewUtils::lazyEditor($this, 'Summary', ['atts' => ['edit' => ['style' => ['minHeight' => '1em']]]]),
	            'details'    => ViewUtils::lazyEditor($this, 'Details', ['atts' => ['edit' => ['style' => ['minHeight' => '1em']]]]),
	        ],
        	$this->filterWidgets()
        );

        $this->mustGetCols = array_merge($this->mustGetCols, ['summary']);
        $subObjects = $this->templatesSubObjects();

        $this->customize($customDataWidgets, $subObjects, $this->filterWidgetsExceptionCols());
    }    
}
?>

