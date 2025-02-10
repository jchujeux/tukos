<?php
namespace TukosLib\Objects\Sports\Strava;

use TukosLib\Objects\Views\Models\ModelsAndViews;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;

trait AuthorizeAndSynchronize {
	
    use ModelsAndViews;
    
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
    public function stravaSynchronize($query, $atts = []){//needs $this->stravaCols(), which must include at least stravaid, startdate, starttime, and $this->activityKpis(), the kpis to compute during synchronization, and $this->itemsModel(), $this->itemsView()
        $athleteId = $query['athleteid'];
        $synchroStreams = $query['synchrostreams'] === 'false' ? false : $query['synchrostreams'];
        $stravaActivitiesModel = Tfk::$registry->get('objectsStore')->objectModel('stravaactivities');
        $itemsModel = $this->itemsModel();
        $itemsView = $this->itemsView();
        if ($itemsValues = $stravaActivitiesModel->activitiesToTukos($athleteId, $query['synchrostart'], $query['synchroend'], $query['synchroweatherstation'], $synchroStreams)){
            $itemsToProcess = []; $kpisToGet = $this->activityKpis();
            if ($defaultItemsCols = $itemsModel->synchroDefaultItemsCols()){
                $presentCols = array_intersect(array_keys($query), $defaultItemsCols);
                $presentValues = Utl::getItems($presentCols, $query);
            }else{
                $presentValues = [];
            }
            foreach ($itemsValues as &$itemValues){
                $itemsToProcess[] = ['kpisToGet' => &$kpisToGet, 'itemValues' => ($itemValues = array_merge($itemValues, $presentValues))];
            }
            $kpis = $itemsModel->computeKpis($athleteId, $itemsToProcess);
            foreach($kpis as $key => $itemKpis){
                unset($itemsValues[$key]['id']);
                $itemsValues[$key] = array_merge(array_diff_key($itemsValues[$key], array_flip($stravaActivitiesModel->tukosStreamCols())), $itemKpis);
            }
            $itemsValues = $this->convert($itemsValues, $itemsView->dataWidgets, 'objToStoreEdit', true);
            $itemsValues = Utl::toAssociativeGrouped($itemsValues, 'startdate');
            foreach ($itemsValues as $values){
                $times = array_column($values, 'starttime');
                array_multisort($times, SORT_ASC, $values);
            }
            $usersItems = Tfk::$registry->get('objectsStore')->objectModel('users')->getAllExtended(['where' => [['col' => 'parentid', 'opr' => 'IN', 'values' => [$query['athleteid'], $query['coachid']]]], 'cols' => ['id', 'parentid']]);
            return ['stravaActivities' => $itemsValues, 'usersItems' => $usersItems];
        }else{
            return ['stravaActivities' => [], 'usersItems' => []];
        }
    }
    public function activitiesServerSynchronize($query){
        list('stravaActivities' => $stravaActivitiesToSync, 'usersItems' => $usersItems) = $this->stravaSynchronize($query);
        list('id' => $programId, 'athleteid' => $athleteId, 'ignoreItemFlag' => $ignoreItemFlag) = $query;
        if(empty($stravaActivitiesToSync)){
            if (!$this->user->isRestrictedUser()){
                Feedback::add($this->tr('Nostravaactivitytosync'));
            }
            return;
        }
        $acl = ['1' => ['userid' => Tfk::tukosBackOfficeUserId, 'permission' => '3']];
        foreach($usersItems as $userItem){
            $acl[] = ['userid' => $userItem['id'], 'permission' => '3'];
        }
        $datesToSync = array_unique(array_column($stravaActivitiesToSync, 'startdate'));
        $itemsModel = $this->itemsModel();
        $sessionsToSync = $itemsModel->getAll(['where' => ['mode' => 'performed', 'parentid' => $programId, ['col' => 'startdate', 'opr' => 'in', 'values' => $datesToSync]], 'cols' => array_merge($this->stravaCols(), ['id', 'sessionid', 'starttime'])]);
        $stravaActivitiesToSync = Utl::toAssociativeGrouped($stravaActivitiesToSync, 'startdate');
        ksort($stravaActivitiesToSync);
        $sessionsToSync = Utl::toAssociativeGrouped($sessionsToSync, 'startdate');
        $createdSessions = $updatedSessions = [];
        foreach($stravaActivitiesToSync as $day => $activities){
            $times = array_column($activities, 'starttime');
            array_multisort($times, SORT_ASC, $activities);
            if ($sessions = Utl::getItem($day, $sessionsToSync)){
                $existingSessionsId = array_column($sessions, 'sessionid');
                $nextSessionId = empty($existingSessionsId) ? 1 : (max($existingSessionsId) + 1);
                $sessions = Utl::toAssociative($sessions, 'starttime');
                foreach ($activities as $activity){
                    if ($session = Utl::getItem($activity['starttime'], $sessions)){
                        if ($updated = $itemsModel->updateOne($this->mergeSession($session, $activity, $ignoreItemFlag))){
                            $updatedSessions[] = $updated['id'];
                        }
                    }else{
                        if (empty($sessions)){
                            $createdSessions[] = $itemsModel->insert(array_merge($activity, ['startdate' => $day, 'sessionid' => $nextSessionId, 'mode' => 'performed', 'parentid' => $programId, 'sportsman' => $athleteId, 'acl' => $acl]), true)['id'];
                                $nextSessionId += 1;
                        }else{
                            if ($updated = $itemsModel->updateOne($this->mergeSession(array_shift($sessions), $activity, $ignoreItemFlag))){
                                $updatedSessions[] = $updated['id'];
                            }
                        }
                    }
                }
            }else{
                $i = 1;
                foreach($activities as $activity){
                    $createdSessions[] = $itemsModel->insert(array_merge($activity, ['startdate' => $day, 'sessionid' => $i, 'mode' => 'performed', 'parentid' => $programId, 'sportsman' => $athleteId, 'acl' => $acl]), true)['id'];
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