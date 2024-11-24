<?php
namespace TukosLib\Objects\Sports\Plans;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Sports\Workouts\Views\Edit\View as WorkoutsEditView;
use TukosLib\Objects\ChartView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;


class View extends AbstractView {
    
    use CalendarsViewUtils, ChartView;
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Sportsman', 'Title');
        $this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
        $tr = $this->tr;
        $this->setGridWidget('sptworkouts');
        $this->allowedNestedWatchActions = 1;
        $this->allowedNestedRowWatchActions = 0;
        $this->addToTranslate(['w', 'dateofday', 'dayofweek', 'weekoftheyear', 'weekofprogram', 'weekendingon', 'newevent', 'sportsmanhasnouserassociatednoacl']);
        $this->namesToTranslate = array_merge(
            ['fromdate', 'duration', 'todate', 'displayeddate', 'stsdays', 'ltsdays', 'initialsts', 'initiallts', 'initialhracwr', 'displayfromdate', 'displayfromsts', 'displayfromlts',
                'startdate', 'intensity', 'stress', 'distance', 'elevationgain', 'sensations', 'perceivedeffort', 'perceivedmechload', 'mood', 'sts', 'lts', 'tsb', 'hracwr', 'timemoving', 'avghr', 'avgpw', 'heartrate_load', 'power_load', 'heartrate_avgload', 'power_avgload',
                'powercalcstream_load', 'avgcadence', 'mechload', 'heartrate', 'power', 'slope', 'avgload', 'load', 'timeabove', 'timebelow', 'loadabove', 'loadbelow', 'threshold', 'timecurve', 'durationcurve', 'shrink', 'performed', 'planned', 'estimatedrawpowerstream'],
            Sports::$sportOptions, Sports::$modeOptions);
        $dateChangeLocalAction = function($serverTrigger) {
            return [
                'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('newValue', '#synchnextmonday'),]],
                'weeklies' => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter'),
                'displayeddate' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return true;"]]
            ];
        };
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['storeArgs' => ['cols' => ['email']], 'onChangeLocalAction' => ['sportsmanemail' => ['value' => "return sWidget.getItemProperty('email');"], 'parentid' => ['localActionStatus' => $this->programAclLocalAction()]]]]],
            'comments' => ['atts' => ['edit' => ['style' => ['height' => '300px']]]],
            'coachid' => ViewUtils::objectSelect($this, 'coach', 'people', ['atts' => [
                'edit' => ['storeArgs' => ['cols' => ['email', 'parentid']],'onWatchLocalAction' => ['value' => [
                    'coachorganization' => ['value' => ['triggers' => ['server' => true, 'user' => true], 'action' => "return sWidget.getItemProperty('parentid');"]],
                    'coachemail' => ['value' => ['triggers' => ['server' => false, 'user' => 'true'], 'action' => "return sWidget.getItemProperty('email');"]],
                    'coachid' => ['localActionStatus' => $this->programAclLocalAction()]
                ]]],
                'overview' => ['hidden' => true]
            ]]),
            'fromdate' => ViewUtils::tukosDateBox($this, 'fromdate', ['atts' => ['edit' => [
                'onChangeLocalAction' => [
                    'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#todate'),true)}" ],
                    'displayfromdate' => ['value' => "return sWidget.valueOf('fromdate');"],
                ]]]]),
            'duration'  =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
                'edit' => [
                    'onChangeLocalAction' => [
                        'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#todate'),true)}" ]
                    ],
                    'unit' => ['style' => ['width' => '6em']/*, 'onWatchLocalAction' => ['value' => "widget.numberField.set('value', parseInt(dutils.convert(widget.numberField.get('value'), oldValue, newValue)));"]*/],
                ],
                'storeedit' => ['formatType' => 'numberunit'],
                'overview' => ['formatType' => 'numberunit', 'hidden' => true],
            ]]),
            'todate'   => ViewUtils::tukosDateBox($this, 'todate', ['atts' => ['edit' => [
                'onChangeLocalAction' => [
                    'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#duration'),false)}" ],
                ],
            ]
            ]
            ]
                ),
            'displayeddate' => $this->displayedDateDescription(['atts' => ['edit' => ['onWatchLocalAction' => ['value' => $dateChangeLocalAction(true)]]]]),
            'googlecalid' => ViewUtils::textBox($this, 'Googlecalid', ['atts' => ['edit' => ['disabled' => true]]]),
            'sportsmanemail' => ViewUtils::textBox($this, 'SportsmanEmail', ['atts' => ['edit' => ['disabled' => true, 'hidden' => true], 'overview' => ['hidden' => true]]]),
            'coachemail' => ViewUtils::textBox($this, 'CoachEmail', ['atts' => ['edit' => ['disabled' => true, 'hidden' => true], 'overview' => ['hidden' => true]]]),
            'coachorganization' => ViewUtils::objectSelect($this, 'CoachOrganization', 'organizations', ['atts' => ['edit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
            'lastsynctime' => ViewUtils::timeStampDataWidget($this, 'Lastsynctime', ['atts' => ['edit' => ['disabled' => true]]]),
            'synchrostart' => ViewUtils::tukosDateBox($this, 'Synchrostart', ['atts' => [
                'edit' => ['onWatchLocalAction' => ['value' => ['synchroweeksbefore' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]]]]],
            ]]),
            'synchroend' => ViewUtils::tukosDateBox($this, 'Synchroend', ['atts' => [
                'edit' => ['onWatchLocalAction' => ['value' => ['synchroweeksafter' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]]]]],
            ]]),
            'synchroweeksbefore' => ViewUtils::tukosNumberBox($this, 'Synchroweeksbefore', ['atts' => [
                'edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => ['synchrostart' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
                    "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(sWidget.valueOf('#displayeddate'))), 'week', -newValue));" ]]]]],
                'overview' => ['hidden' => true]
            ]]),
            'synchroweeksafter' => ViewUtils::tukosNumberBox($this, 'Synchroweeksafter', ['atts' => [
                'edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => ['synchroend' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
                    "var nextMonday = sWidget.valueOf('#synchnextmonday') === 'YES';" .
                    "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(sWidget.valueOf('#displayeddate'))), 'week', nextMonday ? newValue + 1 : newValue));"
                ]]]]],
                'overview' => ['hidden' => true]
            ]]),
            'synchnextmonday' => viewUtils::storeSelect('synchnextmonday', $this, 'Synchnextmonday', null, ['atts' => [
                'edit' => ['style' => ['width' => '4em'], 'onWatchLocalAction' => ['value' => ['synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('#displayeddate', 'newValue')]]]]],
                'overview' => ['hidden' => true]
            ]]),
            'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
            'calendar' => $this->calendarWidgetDescription([
                'type' => 'StoreSimpleCalendar',
                'atts' => ['edit' => [
                    //'date' => date('Y-m-d', strtotime('next monday')),
                    'columnViewProps' => ['minHours' => 0, 'maxHours' => 4, 'style' => ['width' => 'auto']],
                    'style' => ['height' => '320px'],
                    'timeMode' => 'duration', 'durationFormat' => 'time', 'moveEnabled' => true,
                    'customization' => ['items' => [
                        'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru'],
                            'color' => ['field' => 'mode', 'map' => ['planned' => 'white', 'performed' => 'black']],
                            'fontStyle' => ['field' => 'mode', 'map' => ['planned' => 'normal', 'performed' => 'italic']]],
                        'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::$registry->rootUrl . Tfk::$publicDir . 'images/'],
                        'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
                    ]],
                    'onChangeNotify' => [$this->gridWidgetName => [
                        'startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport'/*, 'warmup' => 'warmup',
                        'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown'*/, 'mode' => 'mode'
                    ]],
                    'onWatchLocalAction' => ['date' => $dateChangeLocalAction(false)]]]],
                'fromdate', 'todate'),
            'weeklies' => ViewUtils::JsonGrid($this, 'Weeklies', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'weekof' => viewUtils::tukosDateBox($this, 'Weekof'),
                'athleteweeklyfeeling'    => ViewUtils::textArea($this, 'AthleteWeeklyFeeling'),
                'coachweeklycomments'  => ViewUtils::lazyEditor($this, 'CoachWeeklyComments'),
            ],
                ['atts' => ['edit' => [
                    'sort' => [['property' => 'weekof', 'descending' => true]], 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'weekof', 'endDateTimeCol' => 'weekof',
                    'onWatchLocalAction' => ['allowApplicationFilter' => ['weeklies' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')]],
                    'beforeActions' => ['createNewRow' => $this->weekliesBeforeCreateNewRow()]
                ]]]
                ),
            'stsdays' => ViewUtils::tukosNumberBox($this, 'stsdays', ['atts' => [
                'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '0.'], 'onChangeLocalAction' => ['stsdays' => ['localActionStatus' => $this->tsbParamsChangeAction("'stsdays'")]]],
                'overview' => ['hidden' => true]
            ]]),
            'ltsdays' => ViewUtils::tukosNumberBox($this, 'ltsdays', ['atts' => [
                'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.'], 'onChangeLocalAction' => ['ltsdays' => ['localActionStatus' => $this->tsbParamsChangeAction("'ltsdays'")]]],
                'overview' => ['hidden' => true]
            ]]),
            'initialsts' => ViewUtils::tukosNumberBox($this, 'initialsts', ['atts' => [
                'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##'], 'onChangeLocalAction' => ['initialsts' => ['localActionStatus' => $this->tsbParamsChangeAction("'initialsts'")]], 'hidden' => true],
                'overview' => ['hidden' => true]
            ]]),
            'initiallts' => ViewUtils::tukosNumberBox($this, 'initiallts', ['atts' => [
                'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##'], 'onChangeLocalAction' => ['initiallts' => ['localActionStatus' => $this->tsbParamsChangeAction("'initiallts'")]]],
                'overview' => ['hidden' => true]
            ]]),
            'initialhracwr' => ViewUtils::tukosNumberBox($this, 'initialhracwr', ['atts' => [
                'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#.#'], 'onChangeLocalAction' => ['initialhracwr' => ['localActionStatus' => $this->initialProgressivityChangeAction()]]],
                'overview' => ['hidden' => true]
            ]]),
            'displayfromdate' => ViewUtils::tukosDateBox($this, 'Displayfromdate', ['atts' => ['edit' => [
                'onChangeLocalAction' => [
                    'displayfromdate' => ['localActionStatus' => $this->displayFromDateChangeAction()]
                ]]]]),
            'displayfromsts' => ViewUtils::tukosNumberBox($this, 'Displayfromsts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##']], 'overview' => ['hidden' => true]]]),
            'displayfromlts' => ViewUtils::tukosNumberBox($this, 'Displayfromlts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##']],  'overview' => ['hidden' => true]]]),
        ];
        
        $subObjects = [
            'sptworkouts' => [
                'atts' => [
                    'title' => $this->tr('Workouts'), 'allDescendants' => true, 'allowApplicationFilter' => 'no', 'startDateTimeCol' => 'startdate',
                    'endDateTimeCol' => 'startdate'/*, 'freezeWidth' => true*/, 'minWidth' => '40',
                    'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
                    'onChangeNotify' => [
                        'calendar' => [
                            'startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'mode' => 'mode'
                        ]],
                    'onDropMap' => [
                        'templates' => ['fields' => ['name' => 'name', 'comments' => 'comments', 'startdate' => 'startdate', 'duration' => 'duration', 'intensity' => 'intensity', 'stress' => 'stress',
                            'sport' => 'sport', 'warmup' => 'warmup', 'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode', 'heartrate_load' => 'heartrate_load', 'power_load' => 'power_load'
                        ]],
                        'warmup' => ['mode' => 'update', 'fields' => ['warmup' => 'summary']],
                        'mainactivity' => ['mode' => 'update', 'fields' => ['mainactivity' => 'summary']],
                        'warmdown' => ['mode' => 'update', 'fields' => ['warmdown' => 'summary']],
                    ],
                    'sort' => [['property' => 'startdate', 'descending' => true], ['property' => 'starttime', 'descending' => true]],
                    'onWatchLocalAction' => [
                        'allowApplicationFilter' => ['sptworkouts' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')],
                        'value' => ['sptworkouts' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->sptWorkoutsTsbAction()]]],
                        'collection' => [
                            'sptworkouts' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => false], 'action' => $this->sptWorkoutsTsbAction()]],
                            'calendar' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => 'tWidget.currentView && tWidget.currentView.invalidateLayout();return true;']],
                        ]],
                    'renderCallback' => "if (rowData.mode === 'performed'){domstyle.set(node, 'fontStyle', 'italic');}",
                    'deleteRowAction' => $this->deleteRowAction(),
                    'colsDescription' => [
                        'startdate' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
                        'heartrate_load' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['heartrate_load' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
                        'mode' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['mode' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
                    ],
                    'afterActions' => ['createNewRow' => $this->afterCreateRow(), 'updateRow' => $this->afterUpdateRow(), 'deleteRows' => $this->afterDeleteRows(), '_getValue' => $this->after_getValue()],
                    'beforeActions' => ['createNewRow' => $this->beforeCreateRow(), 'deleteRows' => $this->beforeDeleteRows(), 'updateRow' => $this->beforeUpdateRow()],
                    'noCopyCols' => ['googleid', 'stravaid'],
                    'editActionLayout' => WorkoutsEditView::editDialogLayout(),
                    'translations' => Utl::translations($this->namesToTranslate, $this->tr, 'lowercase')
                ],
                'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@displayfromdate'], ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'],
                    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
                'removeCols' => ['sportsman','grade', 'configstatus', 'kpiscache'],
                'hiddenCols' => ['parentid', 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'starttime', 'googleid', 'mode', 'coachcomments', 'sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'heartrate_avgload', 'power_avgload', 'heartrate_load', 'power_load',
                    'mechload', 'heartrate_timeabove_threshold_90', 'heartrate_timeabove_threshold', 'heartrate_timeabove_threshold_110', 'contextid', 'updated'],
                'ignorecolumns' => ['athleteweeklycomments', 'coachweeklyresponse'] // temporary: these were suppressed but maybe present in some customization items
            ],
            
            'templates' => [
                'object' => 'sptworkouts',
                'atts' => [
                    'title' => $this->tr('workoutstemplates'),
                    'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], /*'freezeWidth' => true, 'minGridWidth' => '600', 'width' => 600,*/
                    'sort' => [['property' => 'name']],
                    'beforeActions' => ['createNewRow' => $this->templatesBeforeCreateNewRow()],
                    'afterActions' => ['createNewRow' => $this->templatesAfterCreateNewRow()],
                    'editActionLayout' => WorkoutsEditView::editDialogLayout(),
                ],
                'filters' => ['grade' => 'TEMPLATE'],
                'initialRowValue' => ['mode' => 'planned'],
                'removeCols' => /*$this->user->isRestrictedUser() ? ['sportsman','googleid', 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'coachcomments', 'comments', 'grade', 'configstatus'] : */['sportsman','grade', 'configstatus', 'kpiscache'],
                'hiddenCols' => ['parentid'/*, 'stress'*/, 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'starttime', 'googleid', 'mode', 'coachcomments', 'sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'hr95', 'heartrate_avgload', 'power_avgload',
                    'heartrate_load', 'power_load', 'mechload', 'heartrate_timeabove_threshold_90', 'heartrate_timeabove_threshold', 'heartrate_timeabove_threshold_110', 'contextid', 'updated'],
                'ignorecolumns' => ['athleteweeklycomments', 'coachweeklyresponse'], // temporary: these were suppressed but maybe present in some customization items
                'allDescendants' => true, 'width' => '400'
            ],
        ];
        $this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'synchrostart', 'synchroend', 'weeklies'],
            'get' => ['displayeddate'],
            'post' => ['displayeddate', 'synchrostart', 'synchroend']], ['weeklies' => [], 'displayfrom' => []]);
    }
    protected function synchroStartLocalAction($displayeddate, $synchnextmonday){
        $displayedDateValue = $displayeddate === "newValue" ? "newValue" : ("sWidget.valueOf('$displayeddate')");
        $synchNextMondayValue = $synchnextmonday === 'newValue' ? "newValue === 'YES'" : ("sWidget.valueOf('$synchnextmonday') === 'YES'");
        return <<<EOT
	dojo.ready(function(){
    	var synchroWeeksBefore = sWidget.valueOf('synchroweeksbefore') || 0, synchroWeeksAfter = sWidget.valueOf('synchroweeksafter') || 0,
    		displayeddate = $displayedDateValue, form = sWidget.form;
    	if (Number.isInteger(synchroWeeksBefore)){
            form.getWidget('synchrostart').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(displayeddate)), 'week', -synchroWeeksBefore)));
    	}
    	if (Number.isInteger(synchroWeeksAfter)){
    		var nextMonday = $synchNextMondayValue;
    		form.getWidget('synchroend').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(displayeddate)), 'week', nextMonday ? synchroWeeksAfter + 1 : synchroWeeksAfter)));
    	}
	});
	return true;
EOT;
    }
    public function preMergeCustomizationAction($response, $customMode){
        return $this->chartPreMergeCustomizationAction($response, $response['dataLayout']['contents']['row2']['contents']['col1']['contents']['rowcharts'], $customMode, 'sptworkouts', 'startdate', 'starttime', ['fromdate', 'todate', 'displayeddate', 'displayfromdate'],
            $this->namesToTranslate, 'displayeddate');
    }
    
    public static function programAclLocalAction(){
        $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
        return <<<EOT
const form = sWidget.form, acl = form.getWidget('acl'), coachId = form.valueOf('coachid'), athleteId = form.valueOf('parentid');
if (!coachId){
    Pmg.setFeedback('needtodefinecoach');
    return false;
}
if (athleteId && coachId){
    Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItems', query: {storeatts: JSON.stringify({where: [{col: 'parentid', opr: 'IN', values: [athleteId, coachId]}], cols: ['id', 'parentid']})}}).then(
    	function (response){
            if (response.data.items.length > 0){
                //acl.set('value', '');
                acl.deleteRows(lang.clone(acl.store.fetchSync()), true);
                acl.addRow(null, {userid: $tukosBackOfficeUserId,permission:"2"});
                if (response.data.items.length === 1){
                        acl.addRow(null, {userid: response.data.items[0].id, permission: "3"});
                }else{
                    response.data.items.forEach(function(item){
                        acl.addRow(null, {userid: item.id, permission: item.parentid == athleteId ? "2" : "3"});
                    });
                }
                let workoutsWidget = form.getWidget('sptworkouts'), idp = workoutsWidget.store.idProperty, workoutsRows = workoutsWidget.store.fetchSync();
                let aclValue = {1: {rowId: 1, userid: $tukosBackOfficeUserId, permission:"3"}}, rowId = 2;
                response.data.items.forEach(function(item){
                    aclValue[rowId] = {rowId: rowId, userid: item.id, permission:"3"};
                    rowId += 1;
                });
                aclValue = JSON.stringify(aclValue);
                workoutsRows.forEach(function(row){
                    const toUpdate = {acl:  aclValue};
                    toUpdate[idp] = row[idp];
                    workoutsWidget.updateRow(toUpdate);
                });
            }else{
                Pmg.setFeedback(Pmg.message('sportsmanorcoachhasnouserassociatednoacl', 'sptplans'), null, null, true);
            }
		}
	);
}else{
    acl.deleteRows(lang.clone(acl.store.fetchSync()), true);
}
return true;
EOT;
    }
    function sptWorkoutsTsbAction(){
        return <<<EOT
require (["tukos/objects/sports/TsbCalculator"], function(TsbCalculator){
    var grid = sWidget, form = grid.form, params = {};
    sWidget.tsbCalculator = this.tsbCalculator || new TsbCalculator({workoutsStore: sWidget.store, form: form, stressProperties: ['heartrate_load', 'heartrate_avgload']});
    sWidget.tsbCalculator.initialize();
    sWidget.tsbCalculator.updateRowAction(sWidget, false, true);
});
return true;
EOT
        ;
    }
    function OpenEditAction(){
        $currentDate = date('Y-m-d');
        return <<<EOT
const fromDate = this.valueOf('fromdate'), toDate = this.valueOf('todate');
if (fromDate && toDate){
    if ('$currentDate' < fromDate){
        this.setValueOf('displayeddate', fromDate);
    }else if('$currentDate' > toDate){
        this.setValueOf('displayeddate', toDate);
    }
}
if (Pmg.isRestrictedUser()){
    const self = this;
    ['parentid', 'name', 'fromdate', 'duration', 'todate', 'initialsts', 'initiallts', 'initialhracwr'].forEach(function(widgetName){
        self.getWidget(widgetName).set('disabled', true);
    });
}
EOT
        ;
    }
    function weekliesBeforeCreateNewRow(){
        return <<<EOT
var row = args || this.clickedRow.data;
row.weekof = this.valueOf('displayeddate');
EOT
        ;
    }
    function templatesBeforeCreateNewRow(){
        $restrictedUserParentTemplatesRowName = $this->tr('restricteduserparenttemplatesrowname');
        return <<<EOT
if (Pmg.isRestrictedUser() && args.name !== "$restrictedUserParentTemplatesRowName"){
    const self = this, row = args, idp = this.collection.idProperty;
    let parentRow = this.store.filter((new this.store.Filter()).eq('name', "$restrictedUserParentTemplatesRowName")).fetchSync();
    if (parentRow.length === 0){
        Pmg.setFeedback(Pmg.message('Cannotcreatenewtemplate'));
        return;
    }else{
        parentRow = parentRow[0];
    }
    row.parentid = parentRow.id;
    row.name = (row.name ? row.name + ' - ' : '') + Pmg.message('newtemplate');
    this.rowIdpToExpand = parentRow[idp];
}
EOT
        ;
    }
    function templatesAfterCreateNewRow(){
        return <<<EOT
if (Pmg.isRestrictedUser() && this.rowIdpToExpand){
    const rowToExpand = this.row(this.rowIdpToExpand);
    if (!rowToExpand.data['hasChildren']){
        rowToExpand.data['hasChildren'] = true;
        this.store.putSync(rowToExpand.data, {overwrite: true});
    }
    this.expand(this.row(this.rowIdpToExpand), true);
    delete this.rowIdpToExpand;
}
EOT
        ;
    }
    function after_getValue(){
        return <<<EOT
if (this.form.tukosAction === 'ObjectSave'){
    const rows = args;
    rows && rows.forEach(function(row){
        utils.forEach(row, function(item, colName){
            if (colName.includes('shrink')){
                delete row[colName];
            }
        });
    });
}
return args;
EOT
        ;
    }
    function beforeCreateRow(){
        return <<<EOT
var row = args || this.clickedRow.data;
if (!this.isBulkRowAction){
    row.sportsman = this.valueOf('parentid');
    if (!row.mode){
        row.mode = (this.form.viewModeOption === 'viewperformed') ? 'performed' : 'planned';
    }
    if (!row.sport){
        row.sport = 'other';
    }
}
EOT
        ;
    }
    public function workoutCreationAclLocalAction(){
        $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
        return <<<EOT
//const self = this;// idp = this.store.idProperty;
const sportsmanId = this.valueOf('parentid'), coachId = this.valueOf('coachid');
const setAcls = function(usersItems){
    let acl = {1: {rowId: 1, userid: $tukosBackOfficeUserId, permission:"3"}}, rowId = 2;
    usersItems.forEach(function(item){
        acl[rowId] = {rowId: rowId, userid: item.id, permission:"3"};
        rowId += 1;
    });
    const toUpdate = {acl:  JSON.stringify(acl)};
    toUpdate[idp] = row[idp];
    self.updateRow(toUpdate);
};
if (self.usersAclCache && self.usersAclCache.sportsmanId === sportsmanId && self.usersAclCache.coachId === coachId){
    if (self.usersAclCache.usersItems.length > 0){
        setAcls(self.usersAclCache.usersItems);
    }
}else{
    Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItems', query: {storeatts: JSON.stringify({where: [{col: 'parentid', opr: 'IN', values: [sportsmanId, coachId]}], cols: ['id', 'parentid'], promote: true})}}).then(
    	function (response){
            if (response.data.items.length > 0){
                setAcls(response.data.items);
            }else{
                Pmg.setFeedback(Pmg.message('sportsmanorcoachhasnouserassociatednoacl', 'sptplans'), null, null, true);
            }
            self.usersAclCache = {sportsmanId: sportsmanId, coachId: coachId, usersItems: response.data.items};
    	}
    );
}
EOT
        ;
    }
    function afterCreateRow(){
        return <<<EOT
const self = this, idp = this.store.idProperty;
if (!this.isBulkRowAction){
    var row = arguments[1][0] || this.clickedRow.data;
    if (!row.startdate){
        return row;
    }
    if (row.mode === 'performed'){
        this.tsbCalculator && this.tsbCalculator.updateRowAction(this, this.store.getSync(row[idp]), true);
    }
    {$this->workoutCreationAclLocalAction()}
    return row;
}
EOT
    ;
    }
    function beforeUpdateRow(){
        return <<<EOT
if (!this.isBulkRowAction){
    var idp = this.collection.idProperty, rowChanges = (args || this.clickedRow.data), rowBeforeChange = this.collection.getSync(rowChanges[idp]);
    this.rowBeforeChange = lang.clone(rowBeforeChange);
}
EOT
        ;
    }
    function afterUpdateRow(){
        return <<<EOT
if (!this.isBulkRowAction){
    var row = arguments[1][0] || this.clickedRow.data, rowBeforeChange = this.rowBeforeChange, startingRow, isPerformed;
    if (rowBeforeChange.mode !== row.mode || rowBeforeChange.startdate !== row.startdate || rowBeforeChange.heartrate_load !== row.heartrate_load){
        if (row.mode === 'performed' || rowBeforeChange.mode === 'performed'){
            if (rowBeforeChange.startdate !== row.startdate){
                startingRow = false;
            }else{
                startingRow = row;
            }
            isPerformed = row.mode === rowBeforeChange.mode ? 'performed' : 'changed';
        }
    }
    if (startingRow !== undefined){
        this.tsbCalculator && this.tsbCalculator.updateRowAction(this, startingRow ? this.store.getSync(startingRow[this.store.idProperty]) : false, true);
    }
    delete this.rowBeforeChange;
}
EOT
        ;
    }
    function beforeDeleteRows(){
        return <<<EOT
        
var tsbCalculator = this.tsbCalculator, iterator = tsbCalculator.sessionsIterator, previousItem, hasPerformedDeleted = false, hasPlannedDeleted = false;
this.isBulkRowAction = true;
if (tsbCalculator.isActive()){
    when(tsbCalculator.getCollection().sort([{property: 'startdate', descending: true}, {property: 'starttime', descending: true}]).fetchSync(), function(data){
		previousItem = iterator.initialize(data, 'last');
        args.forEach(function(row){
            if (row.heartrate_load){
				while (previousItem !== false && previousItem.startdate >= row.startdate){
					previousItem = iterator.previous();
				}
            }
            if (row.mode === 'performed'){
                hasPerformedDeleted = true;
            }else{
                hasPlannedDeleted = true;
            }
        });
    });
    this.deleteRowsBulkParams = {tsbRow: previousItem, hasPerformedDeleted: hasPerformedDeleted, hasPlannedDeleted: hasPlannedDeleted};
}
EOT
        ;
    }
    function afterDeleteRows(){
        return <<<EOT
if (this.deleteRowsBulkParams){
    var params = this.deleteRowsBulkParams;
    if (params.tsbRow !== undefined){
        this.tsbCalculator.bulkDeleteAction(this, params.tsbRow, true);
    }
    delete this.deleteRowsBulkParams;
}
this.isBulkRowAction = false;
this.refresh({keepScrollPosition: true});
EOT
        ;
    }
    function tsbChangeLocalAction(){
        return <<<EOT
if (tWidget.column){
    var grid = tWidget.column.grid, form = grid.form, col = tWidget.column.field, rowIdp = tWidget.row.data[grid.store.idProperty], row = grid.store.getSync(rowIdp);
    if ((col === 'mode' && oldValue === 'performed') || (col === 'startdate' && newValue === '')){
        grid.updateDirty(rowIdp, 'sts', '');
        grid.updateDirty(rowIdp, 'lts', '');
        grid.updateDirty(rowIdp, 'tsb', '');
    }
    if (row.mode === 'performed'){
        grid.tsbCalculator.updateRowAction(grid, (col === 'heartrate_load' || (col === 'mode' && newValue === 'performed')) ? row : false, true);
    }
}
return true;
EOT
        ;
    }
    function tsbParamsChangeAction($reset){
        return <<<EOT
const form = sWidget.form, grid = form.getWidget('sptworkouts');
if ($reset){
    utils.forEach({displayfromdate: 'fromdate', displayfromsts: 'initialsts', displayfromlts: 'initiallts'}, function(source, target){
        form.setValueOf(target, form.valueOf(source));
    });
}
grid.tsbCalculator.initialize();
grid.tsbCalculator.updateRowAction(grid, false, true);
grid.refresh({skipScrollPosition: true});
return true;
EOT
        ;
    }
    function initialProgressivityChangeAction(){
        return <<<EOT
const initialLts = sWidget.form.valueOf('initiallts');
if (initialLts){
    sWidget.form.setValueOf('initialsts', initialLts * newValue);
}
return true;
EOT
        ;
    }
    function displayFromDateChangeAction(){
        $tsbParamsChangeAction = $this->tsbParamsChangeAction('false');
        return <<<EOT
const form = sWidget.form, grid = form.getWidget('sptworkouts'), tsbCalculator = grid.tsbCalculator, displayFromDate = form.valueOf('displayfromdate'), fromDate = form.valueOf('fromdate'), id = form.valueOf('id');
if (id && newValue < oldValue && form.parent.serverFormContent.data.value.displayfromdate > fromDate){
    grid.watchOnChange = false;
    when(form.serverDialog({action: 'Save', query: {id: form.valueOf('id')}}, {displayfromdate: fromDate}, form.get('dataElts'), Pmg.message('actionDone'), false), function(response){
		grid.watchOnChange = true;
        if (response !== false){
			sWidget.form.setValueOf('displayfromdate', displayFromDate);
            tsbCalculator.setDisplayFromStsAndLts(displayFromDate);
            $tsbParamsChangeAction;
		}
	});
return;
}else{
    tsbCalculator.setDisplayFromStsAndLts(displayFromDate);
    $tsbParamsChangeAction;
}
EOT
        ;
    }
    function createRowAction(){
        return <<<EOT
this.tsbCalculator.createRowAction(this, row);
EOT
        ;
    }
    function updateRowAction(){
        return <<<EOT
this.tsbCalculator.updateRowAction(this, row);
EOT
        ;
    }
    function deleteRowAction(){
        return <<<EOT
this.tsbCalculator.deleteRowAction(this, row);
EOT
        ;
    }
    function getActionsToEnableDisable(){
        return array_merge(parent::getActionsToEnableDisable(), ['googlesync', 'stravasync']);
    }
}
?>
