<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Physio\Assesments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Prescription', 'Title');

        $customDataWidgets = [

            'name' => ['atts' => ['edit' => ['style' => ['width' => '30em']]]],
            'parentid' => ['atts' => ['edit' => [
                'onWatchLocalAction' => ['value' => ['progress' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->onPrescriptionChangeLocalAction()]]]]
            ]]],
            'patient' => ViewUtils::objectSelect($this, 'Patient', 'physiopatients', ['atts' => ['edit' => ['placeHolder' => '', 'disabled' => true]]]),
            'prescriptor' => ViewUtils::objectSelect($this, 'Prescriptor', 'people', ['atts' => ['edit' => ['placeHolder' => '', 'disabled' => true]]]),
            'physiotherapist' => ViewUtils::objectSelect($this, 'Physiotherapist', 'people'),
            'assesmenttype' => ViewUtils::storeSelect('assesmentType', $this, 'Assesmenttype', null, ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'name' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return Pmg.itemName(sWidget.valueOf('#patient')) + '-' + sWidget.valueOf('#prescription') + '-' + sWidget.store.data[sWidget.store.index[newValue]].name;" ]],
                    ]],
            ]]]),
            'assesmentdate' => ViewUtils::tukosDateBox($this, 'Assesmentdate'),
            'assesment' => ViewUtils::editor($this, 'Description', ['atts' => ['edit' => ['height' => '800px']]]),
        ];

        $this->customize($customDataWidgets, [], [ 'grid' => ['patient', 'prescriptor'], 'get' => ['patient', 'prescriptor'], 'post' => ['patient', 'prescriptor']]);
    }    
    function onPrescriptionChangeLocalAction(){
        return <<<EOT
return Pmg.serverDialog({object: 'physioassesments', view: 'Edit', action: 'Process', query: {parentid: sWidget.valueOf('parentid'), progress: sWidget.valueOf('assesmenttype'), params: {process: 'getPrescriptionChanged', noget: true}}}).then(function(response){
    utils.forEach(response.data, function(value, widgetName){
        sWidget.setValueOf(widgetName, value);
    });
});
EOT
        ;
    }
}
?>
