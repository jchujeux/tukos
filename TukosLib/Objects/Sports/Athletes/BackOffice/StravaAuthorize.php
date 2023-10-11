<?php
namespace TukosLib\Objects\Sports\Athletes\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
use Strava\API\OAuth;

class StravaAuthorize extends ObjectTranslator{
    function __construct($query){
        parent::__construct('sptathletes');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->athletesModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
        $this->view  = $this->objectsStore->objectView('sptathletes');
        $this->dataWidgets['authenticationmessage'] = ViewUtils::HtmlContent($this, '', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]);
        $this->dataElts = array_values(array_diff(array_keys($this->dataWidgets), ['sportsman'/*, 'startdate'*/]));
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => false],
            'contents' => [
                'row5' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'widgets' => ['authenticationmessage']
                ]
            ]
        ];
        $this->actionLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
                    'widgets' => Tfk::$registry->isMobile ? ['logo'] : ['logo', 'title']
                ]
            ]
        ];
    }
    function getActionWidgets($query){
        $isMobile = $this->isMobile;
        $title = $this->tr('Stravaauthenticationtitle');
        $actionWidgets['title'] = ['type' => 'HtmlContent', 'atts' => ['value' => $this->isMobile ?  $title : '<h1>' . $title . '</h1>']];
        if ($logo = Utl::getItem('logo', $query)){
            $actionWidgets['logo'] = ['type' => 'HtmlContent', 'atts' => ['value' => 
                '<img alt="logo" src="' . $logo . '" style="height: ' . ($isMobile ? '40' : '80') . 'px; width: auto;' . ($isMobile ? 'float: right;' : '') . '">']];
        }
        $query['targetdb'] = rawurlencode($query['targetdb']);
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
    function sendOnSave(){
        return [];
    }
    function sendOnReset(){
        return [];
    }
    function get($query){
        $oauth = new OAuth(array_merge(Tfk::$registry->getOption('strava'), ['redirectUri' => Tfk::$registry->rootUrl . $_SERVER['REQUEST_URI']]));
        if ($error = Utl::getItem('error', $query)){
            return ['authenticationmessage' => $this->tr('errorauthenticating') . '<p>' . $this->tr('stravaauthenticationask', [['substitute', ['organization' => "<b>{$query['organization']}</b>"]]]) . '<center><a href="'. ($oauth->getAuthorizationUrl(['scope' => ['read', 'activity:read', 'activity:read_all']])) .
            '"><img alt="logo" src="' . Tfk::$publicDir . 'images/Logo_Strava.png" style="width: 300px; height: auto;"></a></center>'];
        }
        else if ($code = Utl::getItem('code', $query)){
            $token = $oauth->getAccessToken('authorization_code', ['code' => $code]);
            $count = $this->athletesModel->updateItems(['stravainfo' => json_encode(['access_token' => $token->getToken(), 'refresh_token' => $token->getRefreshToken(), 'expires' => $token->getExpires()])], ['table' => 'people', 'where' => ['id' => $query['peopleid']]]);
            if ($count === 0){
                return ['authenticationmessage' => $this->tr('StravaAuthenticationproblem')];
            }
            return ['authenticationmessage' => $this->tr('StravaAuthenticationthankyou', [['substitute', ['organization' => $query['organization']]]]) .'<center><img alt="logo" src="' . $query['logo'] . '" style="width: 300px; height: auto;"></center>'];
        }else{
            return ['authenticationmessage' => $this->tr('stravaauthenticationask', [['substitute', ['organization' => "<b>{$query['organization']}</b>"]]]) . '<center><a href="'. ($oauth->getAuthorizationUrl(['scope' => ['read', 'activity:read', 'activity:read_all']])) .
                '"><img alt="logo" src="' . Tfk::$publicDir . 'images/Logo_Strava.png" style="width: 300px; height: auto;"></a></center>'];
        }
    }
}
?>
