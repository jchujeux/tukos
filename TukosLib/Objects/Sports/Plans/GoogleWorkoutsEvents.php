<?php
namespace TukosLib\Objects\Sports\Plans;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Utils\Feedback;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;

trait GoogleWorkoutsEvents {
	
    public function googleSynchronize($query, $atts = []){
        $workoutsModel = Tfk::$registry->get('objectsStore')->objectModel('sptworkouts');
        $workoutView = Tfk::$registry->get('objectsStore')->objectView('sptworkouts');
        $this->storeSelectCache = [];
        $id = $query['id'];
        $performedOnly = Utl::getItem('performedonly', $atts);
        $translator = Tfk::$registry->get('translatorsStore');
        $performedPrefix = ' (' . $translator->substituteTranslations( $this->tr('performedprefix')) .  ')';
        $stravaSyncConfiguration = $this->getCombinedCustomization(['id' => $id], 'edit', null, ['widgetsDescription', 'stravasync', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription']);
        if ($custom = Utl::getItem('custom', $query)){
            $stravaSyncConfiguration = Utl::array_merge_recursive_replace($trackingConfiguration, json_decode($custom, true));
        }
        $includeTrackingFormUrl = ($eventFormUrl = Utl::getItem('eventformurl', $stravaSyncConfiguration)) ? $eventFormUrl['atts']['checked'] : true;
        $targetDbString = $workoutFeedback = null;
        $completeTrackingFormUrl = function ($eventDescription) use ($includeTrackingFormUrl, &$targetDbString, &$workoutFeedback){
            if ($includeTrackingFormUrl){
                if (is_null($targetDbString)){
                    $targetDbString = "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared', true));
                    $workoutFeedback = Tfk::$registry->get('translatorsStore')->substituteTranslations($this->tr('WorkoutFeedback'));
                }
                $eventDescription['description'] .= $targetDbString . '">'  .  $workoutFeedback . '</a><br>';
            }
            return $eventDescription;
        };
        if ($includeTrackingFormUrl){
            $synchroFlag = ($synchroFlag = Utl::getItem('synchroflag', $stravaSyncConfiguration)) ? $synchroFlag['atts']['checked'] : true;
            $synchrostreams = ($synchrostreams = Utl::getItem('synchrostreams', $stravaSyncConfiguration)) ? $synchrostreams['atts']['checked'] : false;
            $formPresentation = ($presentation = Utl::getItem('formpresentation', $stravaSyncConfiguration)) ? $presentation['atts']['value'] : '';
            $formVersion = ($version = Utl::getItem('version', $stravaSyncConfiguration)) ? $version['atts']['value'] : $this->defaultWorkoutsTrackingVersion;
        }else{
            $synchroFlag = $synchrostreams = false;
            $synchrostreams = false;
            $formPresentation = '';
            $formVersion = $this->defaultWorkoutsTrackingVersion;
        }
        $where = ['parentid' => $id/*, [['col' => 'duration', 'opr' => '<>', 'values' => '0'], ['col' => 'mode', 'opr' => '<>', 'values' => 'performed']]*/];
        if ($performedOnly){
            $where = array_merge($where, ['mode' => 'performed']);
        }
        if ($minTimeToSync = Utl::getItem('synchrostart', $atts, '', '')){
            $where[] = ['col' => 'startdate', 'opr' => '>=', 'values' => $minTimeToSync];
        }
        if ($maxTimeToSync = Utl::getItem('synchroend', $atts, '', '')){
            $where[] = ['col' => 'startdate', 'opr' => '<=', 'values' => $maxTimeToSync];
        }
        $workoutsToSync = $workoutsModel->getAll([
            'where' => $where/*$this->user->filter($where, 'sptworkouts')*/, 'cols' => ['id', 'name', 'startdate', 'starttime', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid', 'mode', 'sensations', 'athletecomments', 'coachcomments']
        ]);
        $calId = $atts['googlecalid'];
        $existingGoogleEvents = [];
        $callback = function($event) use (&$existingGoogleEvents, $performedOnly, $performedPrefix){
            $extendedProperties = $event->getExtendedProperties();
            if (!$performedOnly || (Utl::drillDown($extendedProperties, ['private', 'performed']) === 'yes')){
                $existingGoogleEvents[($id = $event['id'])] = ['start' => ['date' => $event['start']['date']], 'end' => ['date' => $event['end']['date']], 'summary' => $event['summary'], 'description' => $event['description']];
                if ($colorId = $event['colorId']){
                    $existingGoogleEvents[$id]['colorId'] = $colorId;
                }
                if (!empty($extendedProperties)){
                    $existingGoogleEvents[$id]['extendedProperties'] = ['private' => $extendedProperties['private'], 'shared' => $extendedProperties['shared']];
                }
            }
            return null;
        };
        Calendar::getEventsList([$calId, array_filter(['singleEvents' => true, 'timeMin' => Dutl::toUTC(date('Y-m-d h:i:s', strtotime($minTimeToSync . '+ 1 minute'))), 'timeMax' => Dutl::toUTC(date('Y-m-d', strtotime($maxTimeToSync . ' + 1 day')))])], $callback);
        if (empty($workoutsToSync) && empty($existingGoogleEvents)){
            Feedback::add($this->tr('noworkouttosync'));
        }else{
            $updatedEvents = [];
            $updated = 0;
            $created = 0;
            $deleted = 0;
            if (!empty($workoutsToSync)){
                foreach($workoutsToSync as $key => $workout){
                    $descriptions[$key] = $this->googleDescription($workout, $workoutView, $id, $includeTrackingFormUrl, $synchroFlag, $synchrostreams, $formPresentation, $formVersion);
                }
                $descriptions = json_decode($translator->substituteTranslations(json_encode($descriptions)), true);
                foreach($workoutsToSync as $key => $workout){
                    $eventDescription = ['start' => ['date' => $workout['startdate']], 'end' => ['date' => date('Y-m-d', strtotime($workout['startdate'] . ' +1 day'))], 'summary' => ($workout['mode'] === 'performed' ? $performedPrefix : '') . $workout['name'], 
                        'description' => $descriptions[$key]];
                    if ($workout['mode'] === 'performed'){
                        $eventDescription['extendedProperties'] = ['private' => ['performed' => 'yes'], 'shared' => null];
                    }
                    if (!empty($intensity = $workout['intensity'])){
                        $eventDescription['colorId'] = Calendar::getEventColorId(Sports::$colorNameToHex[Sports::$intensityColorsMap[$intensity]]);
                    }
                    if ((!$googleEventId = Utl::getItem('googleid', $workout)) || empty($existingGoogleEvents[$googleEventId])){
                        $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
                        $created += 1;
                        $workoutsModel->updateOne(['id' => $workout['id'], 'googleid' => $event->getId()]);
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
                            $workoutsModel->updateOne(['id' => $workout['id'], 'googleid' => $event->getId()]);
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
            Feedback::add($this->tr('Googlesynchronizationoutcome') . ' - ' . $this->tr('created') . ': ' .  $created . ' - ' . $this->tr('updated') . ': ' . $updated . ' - ' .$this->tr('deleted') . ': ' . $deleted);
            if (!$performedOnly){
                $this->updateOne(['id' => $id, 'lastsynctime' => date('Y-m-d H:i:s')]);
            }
        }
        return [];
    }

    public function googleSynchronizeOne($programId, $calId, $workoutIdToSync, $synchroFlag, $synchrostreams, $formPresentation, $formVersion){
        $workoutsModel = Tfk::$registry->get('objectsStore')->objectModel('sptworkouts');
        $workoutView = Tfk::$registry->get('objectsStore')->objectView('sptworkouts');
        $this->storeSelectCache = [];
        $id = $programId;
        $includeTrackingFormUrl = true;
        $targetDbString = $workoutFeedback = null;
        $completeTrackingFormUrl = function ($eventDescription) use ($includeTrackingFormUrl, &$targetDbString, &$workoutFeedback){
            if ($includeTrackingFormUrl){
                if (is_null($targetDbString)){
                    $targetDbString = "&targetdb=" . rawurlencode($this->user->encrypt(Tfk::$registry->get('appConfig')->dataSource['dbname'], 'shared', true));
                    $workoutFeedback = Tfk::$registry->get('translatorsStore')->substituteTranslations($this->tr('WorkoutFeedback'));
                }
                $eventDescription['description'] .= $targetDbString . '">'  .  $workoutFeedback . '</a><br>';
            }
            return $eventDescription;
        };
        $workout = $workoutsModel->getOne(['where' => ['id' => $workoutIdToSync],  'cols' => ['id', 'name', 'startdate', 'starttime', 'duration', 'intensity', 'sport', 'stress', 'warmup', 'mainactivity', 'warmdown', 'comments', 'googleid', 'mode', 'sensations', 'athletecomments', 'coachcomments']]);
        $description = $this->googleDescription($workout, $workoutView, $id, $includeTrackingFormUrl, $synchroFlag, $synchrostreams, $formPresentation, $formVersion);
        $isPerformed = $workout['mode'] === 'performed';
        $summary = ($isPerformed ? '(' . $this->tr('performedprefix') . ')' : '') .  $workout['name'];
        $eventDescription = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(
            ['start' => ['date' => $workout['startdate']], 'end' => ['date' => date('Y-m-d', strtotime($workout['startdate'] . ' +1 day'))], 'summary' => $summary, 'description' => $description])), true);
        if (!empty($intensity = $workout['intensity'])){
            $eventDescription['colorId'] = Calendar::getEventColorId(Sports::$colorNameToHex[Sports::$intensityColorsMap[$intensity]]);
        }
        if ($isPerformed){
            $eventDescription['extendedProperties'] = ['private' => ['performed' => 'yes'], 'shared' => null];
        }
        if ((!$googleEventId = Utl::getItem('googleid', $workout)) || empty($event = Calendar::getEvent($calId, $workout['googleid'])) || $event->getStatus() === 'cancelled'){
            $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
            //Feedback::add($this->tr('CreatedGooglecalendarevent') . ': ' . $workout['id']);
            $workoutsModel->updateOne(['id' => $workout['id'], 'googleid' => $event->getId()]);
        }else{
            try {
                if ($includeTrackingFormUrl && ($position = strpos($event['description'], '&targetdb')) !== false){
                    $event['description'] = substr($event['description'], 0, $position);
                }
                $existingEvent = function($event){
                    $extendedProperties = $event->getExtendedProperties();
                    $existingGoogleEvent = ['start' => ['date' => $event['start']['date']], 'end' => ['date' => $event['end']['date']], 'summary' => $event['summary'], 'description' => $event['description']];
                    if ($colorId = $event['colorId']){
                        $existingGoogleEvent['colorId'] = $colorId;
                    }
                    if (!empty($extendedProperties)){
                        $existingGoogleEvent['extendedProperties'] = ['private' => $extendedProperties['private'], 'shared' => $extendedProperties['shared']];
                    }
                    return $existingGoogleEvent;
                };
                if ($eventDescription != $existingEvent($event)){
                    Calendar::updateEvent($calId, $googleEventId, $completeTrackingFormUrl($eventDescription));
                    //Feedback::add($this->tr('UpdatedGooglecalendarevent') . ': ' . $workout['id']);
                }
            } catch (\Exception $e) {
                $event = Calendar::createEvent($calId, $completeTrackingFormUrl($eventDescription));
                //Feedback::add($this->tr('CreatedGooglecalendarevent') . ': ' . $workout['id']);
                $workoutsModel->updateOne(['id' => $workout['id'], 'googleid' => $event->getId()]);
            }
        }
        return [];
    }
    public function googleDescription($workout, $workoutView, $programId, $includeTrackingFormUrl = false, $synchroFlag,  $synchrostreams, $presentation = '', $version = ''){
        if ($workout['mode'] === 'performed'){
            $attCols = ['duration' => 'minutesToHHMM',  'sport' => 'string', 'sensations' => 'String'];
            $contentCols = ['athletecomments', 'coachcomments'];
        }else{
            $attCols = ['duration' => 'minutesToHHMM',  'intensity' => 'StoreSelect', 'sport' => 'string', 'stress' => 'string'];
            $contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'];
        }
        $description = '';
        foreach($attCols as $col => $attType){
            if (!empty($workout[$col])){
                $description .= "<b>{$this->tr($col)}" . ($col === 'duration' && $workout['mode'] != 'performed' ? " {$this->tr('estimated')}</b> (HH:MM): " : "</b>: ") . ($attType === 'StoreSelect'
                    ? Utl::format($workout[$col], $attType, $this->tr,  $workoutView->dataWidgets[$col]['atts']['edit']['storeArgs']['data'], $this->storeSelectCache)
                    : Utl::format($workout[$col], $attType, $this->tr))
                    . '<br>';
            }
        }
        foreach($contentCols as $att){
            if (!empty($workout[$att])){
                $description .= '<b>' . $this->tr($att) . '</b>: '. $workout[$att] . '<br>';
            }
        }
        if ($includeTrackingFormUrl){
            $workoutName = rawurlencode($workout['name']);
            $sport = rawurlencode($workout['sport']);
            $description .= '<a href=' .  Tfk::$registry->rootUrl . '/tukos/index20.php/tukosTrainingPlans/' .
            "Form/backoffice/Edit?object=sptplans&form=WorkoutFeedback&version=$version&parentid=$programId&date={$workout['startdate']}&name=$workoutName&sport=$sport" . 
            ($workout['mode'] === 'performed' ? "&id={$workout['id']}" : '') . (empty($workout['starttime']) ? '' : "&starttime={$workout['starttime']}") .
            ($synchroFlag ? "&synchroflag=$synchroFlag" : '') . ($synchrostreams ? "&synchrostreams=$synchrostreams" : '') . ($presentation ? "&presentation=$presentation" : '');
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