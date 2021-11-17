<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Utils\Feedback;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;

trait GoogleSessionsEvents {
	
	
    public function googleSynchronize($query, $atts = []){
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $sessionView = Tfk::$registry->get('objectsStore')->objectView('sptsessions');
        $this->storeSelectCache = [];
        $id = $query['id'];
        $trackingConfiguration = $this->getCombinedCustomization(['id' => $id], 'edit', null, ['widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription']);
        $includeTrackingFormUrl = ($eventFormUrl = Utl::getItem('eventformurl', $trackingConfiguration)) ? $eventFormUrl['atts']['checked'] : false;
        $targetDbString = $sessionFeedback = null;
        $completeTrackingFormUrl = function ($eventDescription) use ($includeTrackingFormUrl, &$targetDbString, &$sessionFeedback){
            if ($includeTrackingFormUrl){
                if (is_null($targetDbString)){
                    $targetDbString = "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared'));
                    $sessionFeedback = Tfk::$registry->get('translatorsStore')->substituteTranslations($this->tr('SessionFeedback'));
                }
                $eventDescription['description'] .= $targetDbString . '}">'  .  $sessionFeedback . '</a><br>';
            }
            return $eventDescription;
        };
        if ($includeTrackingFormUrl){
            $gcFlag = ($gcFlag = Utl::getItem('gcflag', $trackingConfiguration)) ? $gcFlag['atts']['checked'] : false;
            $logoFile = ($logo = Utl::getItem('formlogo', $trackingConfiguration)) ? $logo['atts']['value'] : '';
            $formPresentation = ($presentation = Utl::getItem('formpresentation', $trackingConfiguration)) ? $presentation['atts']['value'] : '';
            $formVersion = ($version = Utl::getItem('version', $trackingConfiguration)) ? $version['atts']['value'] : $this->defaultSessionsTrackingVersion;
        }else{
            $logoFile = $formPresentation = $formVersion = $targetDbString = '';
            $gcFlag = false;
        }
        $where = ['parentid' => $id/*, [['col' => 'duration', 'opr' => '<>', 'values' => '0'], ['col' => 'mode', 'opr' => '<>', 'values' => 'performed']]*/];
        if ($minTimeToSync = Utl::getItem('synchrostart', $atts, '', '')){
            $where[] = ['col' => 'startdate', 'opr' => '>=', 'values' => $minTimeToSync];
        }
        if ($maxTimeToSync = Utl::getItem('synchroend', $atts, '', '')){
            $where[] = ['col' => 'startdate', 'opr' => '<=', 'values' => $maxTimeToSync];
        }
        $sessionsToSync = $sessionsModel->getAll([
            'where' => $this->user->filter($where, 'sptsessions'), 'cols' => ['id', 'name', 'startdate', 'sessionid', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid', 'mode']
        ]);
        $calId = empty($atts['googlecalid']) ? $atts['googlecalid'] : $atts['googlecalid'];
        $existingGoogleEvents = [];
        $callback = function($event) use (&$existingGoogleEvents){
            $existingGoogleEvents[($id = $event['id'])] = ['start' => ['date' => $event['start']['date']], 'end' => ['date' => $event['end']['date']], 'summary' => $event['summary'], 'description' => $event['description']];
            if ($colorId = $event['colorId']){
                $existingGoogleEvents[$id]['colorId'] = $colorId;
            }
            return null;
        };
        Calendar::getEventsList([$calId, array_filter(['singleEvents' => true, 'timeMin' => Dutl::toUTC(date('Y-m-d h:i:s', strtotime($minTimeToSync . '+ 1 minute'))), 'timeMax' => Dutl::toUTC(date('Y-m-d', strtotime($maxTimeToSync . ' + 1 day')))])], $callback);
        if (empty($sessionsToSync) && empty($existingGoogleEvents)){
            Feedback::add($this->tr('nosessiontosync'));
        }else{
            $updatedEvents = [];
            $updated = 0;
            $created = 0;
            $deleted = 0;
            if (!empty($sessionsToSync)){
                foreach($sessionsToSync as $key => $session){
                    $descriptions[$key] = $this->googleDescription($session, $sessionView, $id, $includeTrackingFormUrl, $gcFlag, $logoFile, $formPresentation, $formVersion);
                }
                $translator = Tfk::$registry->get('translatorsStore');
                $descriptions = json_decode($translator->substituteTranslations(json_encode($descriptions)), true);
                $performedPrefix = ' (' . $translator->substituteTranslations( $this->tr('performedprefix')) .  ')';
                foreach($sessionsToSync as $key => $session){
                    $eventDescription = ['start' => ['date' => $session['startdate']], 'end' => ['date' => date('Y-m-d', strtotime($session['startdate'] . ' +1 day'))], 'summary' => ($session['mode'] === 'performed' ? $performedPrefix : '') . $session['name'], 
                        'description' => $descriptions[$key]];
                    if (!empty($intensity = $session['intensity'])){
                        $eventDescription['colorId'] = Calendar::getEventColorId(Sports::$colorNameToHex[Sports::$intensityColorsMap[$intensity]]);
                    }
                    if ((!$googleEventId = Utl::getItem('googleid', $session)) || empty($existingGoogleEvents[$googleEventId])){
                        $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
                        $created += 1;
                        $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
                    }else{
                        try {
                            $existingGoogleEvent = $existingGoogleEvents[$googleEventId];
                            if ($includeTrackingFormUrl && ($position = strpos($existingGoogleEvent['description'], '&targetdb')) !== false){
                                $existingGoogleEvent['description'] = substr($existingGoogleEvent['description'], 0, $position);
                            }
                            if ($eventDescription != $existingGoogleEvent){
                                Calendar::updateEvent($calId, $googleEventId, $completeTrackingFormUrl($eventDescription));
                                $updated += 1;
                            }
                            $updatedEvents[] = $googleEventId;
                        } catch (\Exception $e) {
                            $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
                            $created +=1;
                            $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
                        }
                    }
                }
            }
            if (!empty($existingGoogleEvents)){
                $eventsToDelete = array_diff(array_keys($existingGoogleEvents), $updatedEvents);
                foreach ($eventsToDelete as $eventId){
                    Calendar::deleteEvent($calId, $eventId);
                    $deleted +=1;
                }
            }
            Feedback::add($this->tr('synchronizationoutcome') . ' - ' . $this->tr('created') . ': ' .  $created . ' - ' . $this->tr('updated') . ': ' . $updated . ' - ' .$this->tr('deleted') . ': ' . $deleted);
            $this->updateOne(['id' => $id, 'lastsynctime' => date('Y-m-d H:i:s')]);
        }
        return [];
    }

    public function googleSynchronizeOne($programId, $calId, $sessionIdToSync, $gcFlag, $logoFile, $formPresentation, $formVersion){
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $sessionView = Tfk::$registry->get('objectsStore')->objectView('sptsessions');
        $this->storeSelectCache = [];
        $id = $programId;
        $includeTrackingFormUrl = true;
        $targetDbString = $sessionFeedback = null;
        $completeTrackingFormUrl = function ($eventDescription) use ($includeTrackingFormUrl, &$targetDbString, &$sessionFeedback){
            if ($includeTrackingFormUrl){
                if (is_null($targetDbString)){
                    $targetDbString = "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared'));
                    $sessionFeedback = Tfk::$registry->get('translatorsStore')->substituteTranslations($this->tr('SessionFeedback'));
                }
                $eventDescription['description'] .= $targetDbString . '}">'  .  $sessionFeedback . '</a><br>';
            }
            return $eventDescription;
        };
        $session = $sessionsModel->getOne(['where' => ['id' => $sessionIdToSync],  'cols' => ['id', 'name', 'startdate', 'sessionid', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid', 'mode']]);
        $description = $this->googleDescription($session, $sessionView, $id, $includeTrackingFormUrl, $gcFlag, $logoFile, $formPresentation, $formVersion);
        $eventDescription = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(
            ['start' => ['date' => $session['startdate']], 'end' => ['date' => date('Y-m-d', strtotime($session['startdate'] . ' +1 day'))], 'summary' => "({$this->tr('performedprefix')}){$session['name']}", 'description' => $description])), true);
        if (!empty($intensity = $session['intensity'])){
            $eventDescription['colorId'] = Calendar::getEventColorId(Sports::$colorNameToHex[Sports::$intensityColorsMap[$intensity]]);
        }
        if ((!$googleEventId = Utl::getItem('googleid', $session)) || empty($event = Calendar::getEvent($calId, $session['googleid'])) || $event->getStatus() === 'cancelled'){
            $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
            $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
        }else{
            try {
                if ($includeTrackingFormUrl && ($position = strpos($event['description'], '&targetdb')) !== false){
                    $event['description'] = substr($event['description'], 0, $position);
                }
                if ($eventDescription != $event){
                    Calendar::updateEvent($calId, $googleEventId, $completeTrackingFormUrl($eventDescription));
                }
            } catch (\Exception $e) {
                $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
                $sessionsModel->updateOne(['id' => $session['id'], 'googleid' => $event->getId()]);
            }
        }
        return [];
    }
    public function googleDescription($session, $sessionView, $programId, $includeTrackingFormUrl = false, $gcFlag,  $logoFile = '', $presentation = '', $version = ''){
        $attCols = ['duration' => 'minutesToHHMM',  'intensity' => 'StoreSelect', 'sport' => 'string', 'stress' => 'string'];
        $contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'];
        $description = '';
        foreach($attCols as $col => $attType){
            if (!empty($session[$col])){
                $description .= "<b>{$this->tr($col)}" . ($col === 'duration' ? " {$this->tr('estimated')}</b> (HH:MM): " : "</b>: ") . ($attType === 'StoreSelect'
                    ? Utl::format($session[$col], $attType, $this->tr,  $sessionView->dataWidgets[$col]['atts']['edit']['storeArgs']['data'], $this->storeSelectCache)
                    : Utl::format($session[$col], $attType, $this->tr))
                    . '<br>';
            }
        }
        foreach($contentCols as $att){
            if (!empty($session[$att])){
                $description .= '<b>' . $this->tr($att) . '</b>: '. $session[$att] . '<br>';
            }
        }
        if ($includeTrackingFormUrl){
            $sessionName = rawurlencode($session['name']);
            $sport = rawurlencode($session['sport']);
            $description .= '<a href="' .  Tfk::$registry->rootUrl . Tfk::$registry->appUrl .
            "Form/backoffice/Edit?object=sptprograms&form=SessionFeedback&version=$version&parentid=$programId&date={$session['startdate']}&name=$sessionName&sport=$sport" . 
            ($session['mode'] === 'performed' ? "&id={$session['id']}" : '') . (empty($session['sessionid']) ? '' : "&sessionid={$session['sessionid']}") .
            ($gcFlag ? "&gcflag=$gcFlag" : '') . ($logoFile ? "&logo=$logoFile" : '') . ($presentation ? "&presentation=$presentation" : '');
        }
        return $description;
    }
    public function createCalendar($query, $atts){
        $newName = $atts['newname'];
        $newCalendarId = Calendar::createCalendar(['summary' => $newName, 'description' => ''], $atts['newacl'])->getId();
        Feedback::add($this->tr('newcalendar') . ': ' . $newCalendarId);
        Tfk::addExtra($newCalendarId, ['name' => $newName, 'label' => $newName . ' (' . $newCalendarId . ')']);
        return ['googlecalid' => $newCalendarId/*, 'acl' => (array) Calendar::getRules([$newCalendarId])*/];
    }
    public function updateAcl($query, $atts){
        return ['acl' => (array) Calendar::updateRules($atts)];
    }
    public function deleteCalendar($query, $atts){
        Calendar::deleteCalendar($atts['googlecalid']);
        if (!empty($query['id'])){
            $this->updateOne(['id' => $query['id'], 'googlecalid' => null, 'lastsynctime' => null]);
        }
        Feedback::add($this->tr('deletedcalendar') . ': ' . $atts['googlecalid']);
        return [];
    }
    public function calendarAcl($query, $atts){
        return ['acl' => (array) Calendar::getRules([$atts['googlecalid']])];
    }
}
?>