<?php
namespace TukosLib\Objects\Sports\Strava;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

trait AuthorizeAndSynchronize {
	
    public function stravaEmailAuthorize($query, $atts = []){
        $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
        $organizationModel = Tfk::$registry->get('objectsStore')->objectModel('organizations');
        $coach = $peopleModel->getOne(['where' => ['id' => $query['coachid']], 'cols' => ['firstname', 'parentid', 'email']]);
        $coachEmail = Utl::getItem('email', $coach);
        $athlete = $peopleModel->getOne(['where' => ['id' => $query['athleteid']], 'cols' => ['firstname', 'email']]);
        $athleteEmail = Utl::getItem('email', $athlete);
        $organization = $organizationModel->getOne(['where' => ['id' => $coach['parentid']], 'cols' => ['name', 'logo']]);
        if (empty($organization)){
            Feedback::add($this->tr('coachneedstobelongtoanorganization'));
        }
        if ($coachEmail && $athleteEmail){
            $logo = ($logo = Utl::getItem('logo', $organization)) ? HUtl::imageUrl($logo) : Tfk::$registry->logo;
            $name = Utl::getItem('name', $organization, 'tukos', 'tukos');
            $authUrlTag = '<a href=' .  Tfk::$registry->rootUrl . Tfk::$registry->appUrl . "Form/backoffice/Edit?object=sptathletes&form=StravaAuthorize&organization={$name}&peopleid={$query['athleteid']}&logo=$logo" .
                "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared', true)) . '>' .
                '<center><div style=\"background-color: gainsboro; color: dodgerblue; font-size:32px; padding-bottom:10px; padding-top:10px; text-align:center; width: 300px;\">' . $this->tr("Gotoaccessauthorizationpage") . '</div></center></a>';
            $translatorsStore = Tfk::$registry->get('translatorsStore');
            $authUrlTag = json_decode($translatorsStore->substituteTranslations(json_encode($authUrlTag)));
            $atts = preg_replace("/\r|\n/", "", Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode([
                'subject' => $this->tr('stravaemailsubject', [['substitute', ['organization' => $name]]]),
                'content' => $this->tr('Stravaemailmessage', [['substitute', ['firstname' => $athlete['firstname'], 'organization' => "<b>{$name}</b>", 'authurltag' => $authUrlTag]]])
                ])));
            $atts = json_decode($atts, true);
            $this->sendContent([], array_merge($atts, ['to' => $athleteEmail, 'cc' => $coachEmail, 'sendas' => 'appendtobody']), ['name' => 'tukosBackOffice']);
        }else{
            if (!$coachEmail){
                Feedback::add($this->tr('coachneedsanemail'));
            }
            if (!$athleteEmail){
                Feedback::add($this->tr('athleteneedsanemail'));
            }
        }
    }
    public function stravaSynchronize($query, $atts = []){//needs $this->stravaCols(), which must include at least stravaid, startdate, starttime
        $athleteId = $query['athleteid'];
        $synchroStreams = $query['synchrostreams'] === 'false' ? false : $query['synchrostreams'];
        $stravaActivitiesModel = Tfk::$registry->get('objectsStore')->objectModel('stravaactivities');
        $targetView = Tfk::$registry->get('objectsStore')->objectView('sptworkouts');
        $stravaCols = $this->stravaCols();
        $stravaActivitiesModel->activitiesToTukos($athleteId, $query['synchrostart'], $query['synchroend'], $synchroStreams, $stravaCols);
        $stravaActivities = $stravaActivitiesModel->getAll(['where' => [['col' => 'startdate', 'opr' => '>=', 'values' => $query['synchrostart']], ['col' =>'startdate', 'opr' => '<=', 'values' => $query['synchroend']]],
            'cols' => $stravaCols, 'orderBy' => ['startdate' =>  'ASC']]);
        $stravaActivities = Utl::objToEdit($stravaActivities, $targetView->dataWidgets);
        $stravaActivities = Utl::toAssociativeGrouped($stravaActivities, 'startdate');
        foreach ($stravaActivities as $activities){
            $times = array_column($activities, 'starttime');
            array_multisort($times, SORT_ASC, $activities);
        }
        return ['stravaActivities' => $stravaActivities];
    }
}
?>