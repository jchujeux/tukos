<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\Sports\Strava as ST;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

trait StravaSynchronize {
	
    public function stravaEmailAuthorize($query, $atts = []){
        $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
        $organizationModel = Tfk::$registry->get('objectsStore')->objectModel('organizations');
        $coach = $peopleModel->getOne(['where' => ['id' => $query['coachid']], 'cols' => ['firstname', 'parentid']]);
        $athlete = $peopleModel->getOne(['where' => ['id' => $query['athleteid']], 'cols' => ['firstname']]);
        $organization = $organizationModel->getOne(['where' => ['id' => $coach['parentid']], 'cols' => ['name', 'logo']]);
        if (empty($organization)){
            Feedback::add($this->tr('coachneedstobelongtoanorganization'));
        }
        $logo = ($logo = Utl::getItem('logo', $organization)) ? HUtl::imageUrl($logo) : Tfk::$registry->logo;
        $name = Utl::getItem('name', $organization, 'tukos', 'tukos');
        $authUrlTag = '<a href=\"' .  Tfk::$registry->rootUrl . Tfk::$registry->appUrl . "Form/backoffice/Edit?object=sptathletes&form=StravaAuthorize&organization={$name}&peopleid={$query['athleteid']}&logo=$logo" .
            "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared', true)) . '\">' .
            '<center><div style=\"background-color: gainsboro; color: dodgerblue; font-size:32px; padding-bottom:10px; padding-top:10px; text-align:center; width: 300px;\">' . $this->tr("Gotoaccessauthorizationpage") . '</div></center></a>';
        $translatorsStore = Tfk::$registry->get('translatorsStore');
        $authUrlTag = json_decode($translatorsStore->substituteTranslations(json_encode($authUrlTag)));
        $atts = preg_replace("/\r|\n/", "", Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode([
            'subject' => $this->tr('stravaemailsubject', [['substitute', ['organization' => $name]]]),
            'content' => $this->tr('Stravaemailmessage', [['substitute', ['firstname' => $athlete['firstname'], 'organization' => "<b>{$name}</b>", 'authurltag' => $authUrlTag]]])
            ])));
        $atts = json_decode($atts, true);
        $this->sendContent([], array_merge($atts, ['to' => $query['athleteemail'], 'cc' => $query['coachemail'], 'sendas' => 'appendtobody']), /*['parentid' => $this->user->id()]*/['name' => 'tukosBackOffice']);
    }
    public function stravaProgramSynchronize($query, $atts = []){
        $programId = $query['id'];
        $athleteId = $query['parentid'];
        $ignoreSessionValues = $query['ignoresessionflag'] === 'false' ? false : $query['ignoresessionflag'];
        $synchroStreams = $query['synchrostreams'] === 'false' ? false : $query['synchrostreams'];
        $updator = Utl::getItem('updator', $query, $this->user->id());
        try{
            $client = ST::getAthleteClient($athleteId);
            $sessionsActivitiesToSync = [];
            $stravaActivitiesToSync = $client->getAthleteActivities(strtotime(DUtl::dayAfter($query['synchroend'])), strtotime($query['synchrostart']));
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
            Feedback::add($this->tr('Noactivitytosync'));
            return;
        }
        $athleteParams = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'h4timethreshold', 'h5timethreshold', 'ftp', 'speedthreshold', 'sex']]);
        $metricsToExtract = ST::allMetrics();
        $datesToSync = [];
        foreach ($stravaActivitiesToSync as $activity){
            $sessionActivity = ST::activityToSession($activity);
            $datesToSync = array_unique(array_merge($datesToSync, [$sessionActivity['startdate']]));
            $sessionsActivitiesToSync[] = $sessionActivity;
        }
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $sessionsToSync = $sessionsModel->getAll(['where' => ['mode' => 'performed', 'parentid' => $programId, ['col' => 'startdate', 'opr' => 'in', 'values' => $datesToSync]], 'cols' => array_merge($metricsToExtract, ['id', 'sessionid', 'starttime'])]);
        $sessionsActivitiesToSync = Utl::toAssociativeGrouped($sessionsActivitiesToSync, 'startdate');
        $sessionsToSync = Utl::toAssociativeGrouped($sessionsToSync, 'startdate');
        $acl = ['1' => ['rowId' => 1, 'userid' => $updator, 'permission' => '3'], '2' => ['rowId' => 2, 'userid' => Tfk::tukosBackOfficeUserId, 'permission' => '3']];
        $createdSessions = $updatedSessions = [];
        foreach($sessionsActivitiesToSync as $day => $activities){
            $times = array_column($activities, 'starttime');
            array_multisort($times, SORT_ASC, $activities);
            if ($sessions = Utl::getItem($day, $sessionsToSync)){
                $existingSessionsId = array_column($sessions, 'sessionid');
                $nextSessionId = empty($existingSessionsId) ? 1 : (max($existingSessionsId) + 1);
                $sessions = Utl::toAssociative($sessions, 'starttime');
                foreach ($activities as $activity){
                    if ($session = Utl::getItem($activity['starttime'], $sessions)){
                        if ($updated = $sessionsModel->updateOne(ST::addStreamsAndMetrics($this->mergeSession($session, $activity, $ignoreSessionValues), $athleteParams, $synchroStreams && !$sessionsModel->hasStreams($session['id']), $sessionsModel->streamCols, $client))){
                            $updatedSessions[] = $updated['id'];
                        }
                    }else{
                        if (empty($sessions)){
                            $createdSessions[] = $sessionsModel->insert(ST::addStreamsAndMetrics(array_merge($activity, ['startdate' => $day, 'sessionid' => $nextSessionId, 'mode' => 'performed', 'parentid' => $programId, 'sportsman' => $athleteId, 'acl' => $acl]),
                                $athleteParams, $synchroStreams, $sessionsModel->streamCols, $client), true)['id'];
                            $nextSessionId += 1;
                        }else{
                            if ($updated = $sessionsModel->updateOne(ST::addStreamsAndMetrics($this->mergeSession(array_shift($sessions), $activity, $ignoreSessionValues), $athleteParams, $synchroStreams && !$sessionsModel->hasStreams($session['id']), $sessionsModel->streamCols, $client))){
                                $updatedSessions[] = $updated['id'];
                            }
                        }
                    }
                }
            }else{
                $i = 1;
                foreach($activities as $activity){
                    $createdSessions[] = $sessionsModel->insert(ST::addStreamsAndMetrics(array_merge($activity, ['startdate' => $day, 'sessionid' => $i, 'mode' => 'performed', 'parentid' => $programId, 'sportsman' => $athleteId, 'acl' => $acl]), 
                        $athleteParams, $synchroStreams, $sessionsModel->streamCols, $client), true)['id'];
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
        if (empty($updatedSessions) && empty($createdSessions)){
            Feedback::add($this->tr('Nosessioncreatedorupdated'));
        }else if (Utl::getItem('googlecalid', $query, false)){
            $this->googleSynchronize(['id' => $programId], ['synchrostart' => $query['synchrostart'], 'synchroend' => $query['synchroend'], 'googlecalid' => $query['googlecalid'], 'performedonly' => true]);
        }
    }
    function mergeSession($session, $activity, $ignoreSessionValues){
        if ($ignoreSessionValues){
            $session = array_merge($session, $activity);
        }else{
            foreach($activity as $col => $value){
                $session[$col] = Utl::getItem($col, $session, $value, $value);
            }
        }
        return $session;
    }
}
?>