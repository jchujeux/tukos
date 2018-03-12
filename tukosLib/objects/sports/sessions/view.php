<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Sports\Sessions\templatesViewMixin;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    use templatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = array_merge([
	            'name'      => ['atts' => ['edit' =>  ['label' => 'Theme', 'style' => ['width' => '30em']]],],
	            'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
	            'duration'          =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
	                        'edit' => [],
	                        'storeedit' => ['formatType' => 'numberunit'],
	                        'overview' => ['formatType' => 'numberunit'],
	                    ]
	                ]
	            ),
	            'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Intensity'),
	            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport', ['atts' => ['edit' => [
	                    'onWatchLocalAction' => ['value' => [
	                        'intensity' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
	                        'stress' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
	                    ]],
	            ]]]),
	            'stress'        => ViewUtils::storeSelect('stress', $this, 'Mechanical stress'),
	            'warmup'    => ViewUtils::lazyEditor($this, 'warmup', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
	            'mainactivity'    => ViewUtils::lazyEditor($this, 'mainactivity', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
	            'warmdown'    => ViewUtils::lazyEditor($this, 'warmdown', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
	            'sessionid' => ViewUtils::textBox($this, 'Sessionid', ['atts' => ['edit' => [ 'style' => ['width' => '5em']]]]),
	        	'sportsman' => ViewUtils::objectSelect($this, 'Sportsman', 'people'),
	            'difficulty'     => ViewUtils::storeSelect('intensity', $this, 'Difficulty'),
	        	'warmupdetails'    => ViewUtils::lazyEditor($this, 'warmupdetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
	            'mainactivitydetails'    => ViewUtils::lazyEditor($this, 'mainactivitydetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
	            'warmdowndetails'    => ViewUtils::lazyEditor($this, 'warmdowndetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
	            'googleid' => ViewUtils::textBox($this, 'Googleid'),
	        ],
        	$this->filterWidgets()
        );

        $this->mustGetCols = array_merge($this->mustGetCols, ['duration', 'intensity', 'stress', 'sport','warmup', 'mainactivity', 'warmdown', 'comments']);

        $subObjects = $this->templatesSubObjects();

        $this->customize($customDataWidgets, $subObjects, $this->filterWidgetsExceptionCols());
    }    
}
?>

