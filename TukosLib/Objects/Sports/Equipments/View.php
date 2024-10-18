<?php
namespace TukosLib\Objects\Sports\Equipments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Sportsman', 'Description');
        $customDataWidgets = [
            'stravagearid' => ViewUtils::textBox($this, 'StravaGearId', [
                'atts' => ['edit' => ['onWatchLocalAction' => ['value' => ['stravagearlink' => ['value' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->gearIdChangeLocalAction()]]]]]],
                'objToOverview' => ['stravaGearLink' => ['class' => $this]]
            ]),
            'stravagearlink' => ViewUtils::htmlContent($this, 'Stravalink', ['atts' => ['edit' => ['disabled' => true]], 'objToEdit' => ['stravaLink' => ['class' => $this]]]),
            'extraweight' => ViewUtils::tukosNumberBox($this, 'extraweight', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.0']]]]),
            'frictioncoef' => ViewUtils::tukosNumberBox($this, 'frictioncoef', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.0000']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.####']],
                'overview' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.####']]]]),
            'dragcoef' => ViewUtils::tukosNumberBox($this, 'dragcoef', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.00']]]]),
        ];

        $this->customize($customDataWidgets, [], ['grid' => ['stravalink']]);
    }
    function stravaGearLink($stravaGearId){
        if (!empty($stravaGearId)){
            if ($stravaGearId[0] === 'b'){
                return "<a href=\"https://www.strava.com/bikes/" . substr($stravaGearId, 1) . "\" target=\"_blank\">$stravaGearId</a>";
            }else{
                return $stravaGearId;
            }
        }
    }
    function gearIdChangeLocalAction(){
        return <<<EOT
if (newValue){
    if (newValue[0] === 'b'){
        return "<a href=\"https://www.strava.com/bikes/" + newValue.substring(1) + "\" target=\"_blank\">" + newValue + "</a>"
    }
}
EOT
        ;
    }
}
?>

