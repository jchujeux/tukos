<?php
namespace TukosLib\Objects\Physio\Patients;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Lastname');
        $customDataWidgets = [
            'firstname'  => ViewUtils::textBox($this, 'Firstname'),
            'email'      => ViewUtils::textBox($this, 'email', ['atts' => ['edit' =>  ['placeHolder' => 'xxx@yyy']]]),
            'telmobile'  => ViewUtils::textBox($this, 'Telephone'),
            'sex'        => ViewUtils::storeSelect('sex', $this, 'Sex'),
            'birthdate'  => ViewUtils::tukosDateBox($this, 'Birthdate', ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'age' => ['value' => ['triggers' => ['server' => true, 'user' => true], 'action' => "var age = dutils.age(newValue);  return (isNaN(age) ? '' : age);"]],
                    ]],
            ]]]),
            'socialsecuid'  => ViewUtils::textBox($this, 'Socialsecuid'),
            'profession' => ViewUtils::textBox($this, 'Profession'),
            'hobbies' => ViewUtils::textBox($this, 'Hobbies', ['atts' => ['edit' => ['style' => ['width' => '40em']]]]),
            'maritalstatus' => ViewUtils::textBox($this, 'Maritalstatus'),
            'laterality'  => ViewUtils::storeSelect('laterality', $this, 'Laterality'),
            'height' => ViewUtils::tukosNumberBox($this, 'Height', ['atts' => ['edit' => ['title' => $this->tr('Height') . '(m)', 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.00'],
                    'onWatchLocalAction' => ['value' => [
                        'imc' => ['value' => ['triggers' => ['server' => true, 'user' => true], 'action' => "var weight = sWidget.form.valueOf('weight'); if (weight > 0 && newValue > 0){return weight / newValue / newValue;}else{return ''}"]],
                    ]],
            ]]]),
            'weight' => ViewUtils::tukosNumberBox($this, 'Weight', ['atts' => ['edit' => [
                    'title' => $this->tr('Weight') . '(kg)',
                    'style' => ['width' => '5em'],
                    'onWatchLocalAction' => ['value' => [
                        'imc' => ['value' => ['triggers' => ['server' => true, 'user' => true], 'action' => "var height = sWidget.form.valueOf('height'); if (height > 0 && newValue > 0){return newValue / height / height;}else{return ''}"]],
                    ]],
            ]]]),
            'corpulence'  => ViewUtils::storeSelect('corpulence', $this, 'Corpulence'),
            'morphotype' => ViewUtils::storeSelect('morphotype', $this, 'Morphotype'),
            'antecedents' => ViewUtils::lazyEditor($this, 'Antecedents', ['atts' => ['edit' => ['height' => '100px']]]),
            'age' => ViewUtils::textBox($this, 'Age', ['atts' => ['edit' => [ 'style' => ['width' => '5em'], 'disabled' => true]]]),
            'imc' => ViewUtils::tukosNumberBox($this, 'IMC', ['atts' => ['edit' => ['disabled' => true, 'constraints' => ['pattern' => '00.']]]]),
            'hrmin' => ViewUtils::tukosNumberBox($this, 'Hrmin'),
            'hrmax' => ViewUtils::tukosNumberBox($this, 'Hrmax', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                'hrmax' => ['localActionStatus' => ['action' => $this->hrMaxLocalAction()]]]]]]]),
            'hrthreshold' => ViewUtils::tukosNumberBox($this, 'Hrthreshold'),
            'h4timethreshold' => ViewUtils::tukosNumberBox($this, 'H4timethreshold'),
            'h5timethreshold' => ViewUtils::tukosNumberBox($this, 'H5timethreshold'),
            'ftp' => ViewUtils::tukosNumberBox($this, 'Ftp'),
            'speedthreshold' => ViewUtils::tukosNumberBox($this, 'Speedthreshold', ['atts' => ['edit' => ['title' => $this->tr('speedthreshold') . ' (km/h)', 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.0'],
            ]]]),
            'stravainfo' => viewUtils::textBox($this, 'stravainfo', ['atts' => ['edit' => ['hidden' => true, 'style' => ['width' => '40em']]]])
        ];
        $subObjects = [
         'sptprograms' => [
	            'atts'  => ['title' => $this->tr('Sptprograms'),],
	            'filters' => ['parentid' => '@id'],
	            'allDescendants' => true,
	        ],
	        'bustrackinvoicescustomers' => [
	            'atts'  => ['title' => $this->tr('bustrackinvoices'),],
	            'filters' => ['parentid' => '@id'],
	            'allDescendants' => true,
	        ],
             'physiocdcs' => [
	            'atts'  => ['title' => $this->tr('Physiocdcs'),],
	            'filters' => ['parentid' => '@id'],
	            'allDescendants' => true,
	        ],
             'physioassesments' => [
	            'atts'  => ['title' => $this->tr('Physioassesments'),],
	            'filters' => [[['col' => 'parentid', 'opr' => '=', 'values' => '@id'], ['col' => 'parentid', 'opr' => 'IN SELECT', 'values' => ['where' => ['parentid' => '@id'], 'table' => 'tukos', 'cols' => ['id']], 'or' => true]]],
	            'allDescendants' => true,
	        ],
             'calendarsentries' => [
    			'atts' => ['title' => $this->tr('Appointments'), 'maxHeight' => '300px', 'storeType' => 'LazyMemoryTreeObjects',],
    			'initialRowValue' => ['duration' => '[1, "hour"]'],
    			'filters' => [
    				'#sources' => [
    					['source' => 'google', 'googleid' => 'jchujeux@gmail.com', 'where' => [
    							['sharedExtendedProperty' => ['tukosgrandparentid' => '@id']], ['sharedExtendedProperty' => ['tukosparentid' => '@id']],
    					]],
    					['source' => 'tukos', 'where' => [[
    						['col' => 'parentid', 'opr' => '=', 'values' => '@id'],
    						['col' => 'parentid', 'opr' => 'IN SELECT', 'values' => ['where' => ['parentid' => '@id'], 'table' => 'tukos', 'cols' => ['id']], 'or' => true]]
    					]]
    				], 
    				[['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]],
    				'enddatetime' => ['>', '@periodstart'], 'startdatetime' =>  ['<', '@periodend'], 
    				'&initSource' =>
	    							"var sources = grid.form.getWidget('sources'), collection = sources.collection, idp = collection.idProperty, dirty = sources.dirty;\n" .
	    							"console.log('in filter for initializing source');\n" .
	    							"collection.fetchSync().some(function(sourceItem){\n" .
	    								"var idv = sourceItem[idp], dirtyItem = dirty[idv] || {};\n" .
	    								"if (dirtyItem.hasOwnProperty('selected') ? dirtyItem.selected : sourceItem.selected){\n" .
	    									"if((dirtyItem.source || sourceItem.source)=== 'tukos'){\n" .
	    										"item.parentid = dirtyItem.hasOwnProperty('tukosparent') ? dirtyItem.tukosparent : sourceItem.tukosparent;\n" .
	    									"}else{\n" .
	    										"item.googlecalid = dirtyItem.hasOwnProperty('googleid') ? dirtyItem.googleid : sourceItem.googleid;\n" .
	    									"}\n" .
	    									"return true;\n" .
	    								"}\n" .
	    							"});\n;"

    			],
    		],

            'physioprescriptions' => [
	            'atts'  => ['title' => $this->tr('Prescriptions'),],
	            'filters' => ['parentid' => '@id'],
	            'allDescendants' => true,
	        ],
        ];

        $this->customize($customDataWidgets, $subObjects, [ 'grid' => ['age', 'imc'], 'get' => ['age', 'imc'], 'post' => ['age', 'imc']]);
    }  
    function hrMaxLocalAction(){
        return <<<EOT
const form = sWidget.form, hrMin = form.valueOf('hrmin'); 
if (hrMin){
    form.setValueOf('hrthreshold', 0.8*(newValue-hrMin) + hrMin);
    form.setValueOf('h4timethreshold', 0.7*(newValue-hrMin) + hrMin);
    form.setValueOf('h5timethreshold', 0.85*(newValue-hrMin) + hrMin);
    return true;
}else{
    return false;
}
EOT
        ;
    }
}
?>
