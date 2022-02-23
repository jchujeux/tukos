<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\Sports\Strava as ST;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

trait StravaSynchronize {
	
    public function stravaEmailAuthorize($query, $atts = []){
        //{athleteid: athleteId, athleteemail: form.valueOf('sportsmanemail'), coachid: form.valueOf('coach'), coachemail: form.valueOf('coachemail'),
        $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
        $organizationModel = Tfk::$registry->get('objectsStore')->objectModel('organizations');
        $coach = $peopleModel->getOne(['where' => ['id' => $query['coachid']], 'cols' => ['firstname', 'parentid']]);
        $athlete = $peopleModel->getOne(['where' => ['id' => $query['athleteid']], 'cols' => ['firstname']]);
        $organization = $organizationModel->getOne(['where' => ['id' => $coach['parentid']], 'cols' => ['name', 'logo']]);
        $logo = ($logo = Utl::getItem('logo', $organization)) ? HUtl::imageUrl($logo) : Tfk::$registry->logo;
        $authUrlTag = '<a href=\"' .  Tfk::$registry->rootUrl . Tfk::$registry->appUrl . "Form/backoffice/Edit?object=sptathletes&form=StravaAuthorize&organization={$organization['name']}&peopleid={$query['athleteid']}&logo=$logo" .
            //"&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared')) . '\"><img alt=\"logo\" src=\"' . $logo . '\"></a>';
            "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared')) . '\">' .
            '<center><div style=\"background-color: gainsboro; color: dodgerblue; font-size:32px; padding-bottom:10px; padding-top:10px; text-align:center; width: 300px;\">' . $this->tr("Gotoaccessauthorizationpage") . '</div></center></a>';
        $translatorsStore = Tfk::$registry->get('translatorsStore');
        $authUrlTag = json_decode($translatorsStore->substituteTranslations(json_encode($authUrlTag)));
        $atts = preg_replace("/\r|\n/", "", Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode([
            'subject' => $this->tr('stravaemailsubject', [['substitute', ['organization' => $organization['name']]]]),
            'content' => $this->tr('Stravaemailmessage', [['substitute', ['firstname' => $athlete['firstname'], 'organization' => "<b>{$organization['name']}</b>", 'authurltag' => $authUrlTag]]])
            ])));
        $atts = json_decode($atts, true);
        $this->sendContent([], array_merge($atts, ['to' => $query['athleteemail'], 'cc' => $query['coachemail'], 'sendas' => 'appendtobody']), ['parentid' => $this->user->id()]);
    }
    public function stravaProgramSynchronize($query, $atts = []){
        $programId = $query['id'];
        $athleteId = $query['parentid'];
        $ignoreSessionValue = $query['ignoresessionflag'];
        try{
            $client = ST::getAthleteClient($athleteId);
            $sessionsActivitiesToSync = [];
            $stravaActivitiesToSync = $client->getAthleteActivities(strtotime($query['synchroend']), strtotime($query['synchrostart']));
        } catch(\Exception $e){
            $message = $e->getMessage();
            if (strpos($message, "Authorization Error") > 0){
                Feedback::add($this->tr('Stravaauthorizationnomorevalid'));
                Tfk::$registry->get('objectsStore')->objectModel('people')->updateOne(['id' => $athleteId, 'stravainfo' => null]);
            }else{
                Feedback::add($this->tr('Couldnotretrievefromstrava') . ': ' . $message);
            }
            return;
        }
        if(empty($stravaActivitiesToSync)){
            Feedback::add($this->tr('Noactivitytosyncforathlete') . '  ' . $athleteId);
            return;
        }
        $metricsToExtract = ST::allMetrics();
        $datesToSync = [];
        foreach ($stravaActivitiesToSync as $activity){
            $sessionActivity = ST::activityToSession($activity);
            $datesToSync = array_unique(array_merge($datesToSync, [$sessionActivity['startdate']]));
            $sessionsActivitiesToSync[] = $sessionActivity;
        }
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $sessionsToSync = $sessionsModel->getAll(['where' => ['mode' => 'performed', 'parentid' => $programId, ['col' => 'startdate', 'opr' => 'in', 'values' => $datesToSync]], 'cols' => array_merge($metricsToExtract, ['id', 'sessionid'])]);
        $sessionsActivitiesToSync = Utl::toAssociativeGrouped($sessionsActivitiesToSync, 'startdate');
        $sessionsToSync = Utl::toAssociativeGrouped($sessionsToSync, 'startdate');
        foreach($sessionsActivitiesToSync as $day => $activities){
            $times = array_column($activities, 'starttime');
            array_multisort($times, SORT_ASC, $activities);
            if ($sessions = Utl::getItem($day, $sessionsToSync)){
                $sessions = Utl::toAssociative($sessions, 'sessionid');
                $i = 1;
                foreach ($activities as $activity){
                    unset($activity['starttime']);
                    if ($session = Utl::getItem($i, $sessions)){
                        if ($ignoreSessionValue){
                            $session = array_merge($session, $activity);
                        }else{
                            foreach($activity as $col => $value){
                                $session[$col] = Utl::getItem($col, $session, $value, $value);
                            }
                        }
                        if ($sessionsModel->updateOne($session)){
                            $updatedSessions[] = $session['id'];
                        }
                    }else{
                        $createdSessions[] = $sessionsModel->insert(array_merge($activity, ['startdate' => $day, 'sessionid' => $i, 'mode' => 'performed', 'parentid' => $programId]))['id'];
                    }
                    $i += 1;
                }
            }else{
                $i = 1;
                foreach($activities as $activity){
                    unset($activity['starttime']);
                    $createdSessions[] = $sessionsModel->insert(array_merge($activity, ['startdate' => $day, 'sessionid' => $i, 'mode' => 'performed', 'parentid' => $programId]))['id'];
                    $i += 1;
                }
            }
        }
        if (!empty($updatedSessions)){
            Feedback::add($this->tr('UpdatedSessions') . ': ' . json_encode($updatedSessions));
        }
        if (!empty($createdSessions)){
            Feedback::add($this->tr('createdSessions') . ': ' . json_encode($createdSessions));
        }
        if (empty($udatedSessions) && empty($createdSessions)){
            Feedback::add($this->tr('Nosessioncreatedorupdated'));
        }
    }
}
?>