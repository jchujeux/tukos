<?php
namespace TukosLib\Objects\Physio\PersoTrack\Treatments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {
    
    use CalendarsViewUtils, QSMChart;
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'PhysioPersoPlan', 'Description');
        $this->addToTranslate(['dateofday', 'dayoftreatment']);
        $this->doNotEmpty = ['displayeddate'];
        //$tr = $this->tr;
        $this->setGridWidget('physiopersosessions');
        $this->allowedNestedWatchActions = 0;
        $this->allowedNestedRowWatchActions = 0;
        $dateChangeLocalAction = function($serverTrigger){
            return [
                'qsmchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->dateChangeChartLocalAction()]],
                'weeklies' => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter')
            ];
        };
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['onChangeLocalAction' => ['parentid' => ['localActionStatus' =>$this->relatedPlanAction()]]]]],
            'patient' => ViewUtils::objectSelect($this, 'Patient', 'people'),
            'comments' => ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple', 'hidden' => true]]],
            'fromdate' => ViewUtils::tukosDateBox($this, 'Begins on', ['atts' => ['edit' => [
                'onChangeLocalAction' => [
                    'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#todate'),true)}" ],
                    'qsmchart' => ['localActionStatus' => $this->chartLocalAction()],
                ]]]]),
            'duration'  =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
                'edit' => [
                    'onChangeLocalAction' => [
                        'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#todate'),true)}" ]
                    ],
                    'unit' => ['style' => ['width' => '6em'], 'onWatchLocalAction' => ['value' => "widget.numberField.set('value', dutils.convert(widget.numberField.get('value'), oldValue, newValue));"]],
                ],
                'storeedit' => ['formatType' => 'numberunit'],
                'overview' => ['formatType' => 'numberunit'],
            ]]),
            'todate'   => ViewUtils::tukosDateBox($this, 'Ends on', ['atts' => ['edit' => [
                'onChangeLocalAction' => [
                    'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#duration'),true)}" ],
                    'qsmchart' => ['localActionStatus' => $this->chartLocalAction()],
                ],
            ]]]),
            'displayeddate' => $this->displayedDateDescription(['atts' => ['edit' => ['onWatchLocalAction' => ['value' => $dateChangeLocalAction(true)]]]]),
            'calendar' => $this->calendarWidgetDescription([
                'type' => 'StoreSimpleCalendar',
                'atts' => ['edit' => [
                    'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
                    'style' => ['height' => '130px', 'width' => '800px'],
                    'timeMode' => 'duration', 'durationFormat' => 'time', 'moveEnabled' => true,
/*
                    'customization' => ['items' => [
                        'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru'],
                            'color' => ['field' => 'mode', 'map' => ['planned' => 'white', 'performed' => 'black']],
                            'fontStyle' => ['field' => 'mode', 'map' => ['planned' => 'normal', 'performed' => 'italic']]],
                        'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::$publicDir . 'images/'],
                        'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
                    ]],
*/
                    'onChangeNotify' => [$this->gridWidgetName => ['startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'exerciseid' => 'exerciseid', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']],
                    'onWatchLocalAction' => ['date' => $dateChangeLocalAction(false)]]]],
                'fromdate', 'todate'),
            'weeklies' => ViewUtils::JsonGrid($this, 'Weeklies', [
                    'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'weekof' => viewUtils::tukosDateBox($this, 'Weekof'),
                    'patientweeklyfeeling'    => ViewUtils::textArea($this, 'PatientWeeklyFeeling'),
                    'therapistweeklycomments'  => ViewUtils::textArea($this, 'TherapistWeeklyComments'),
                ],
                ['atts' => ['edit' => [
                    'sort' => [['property' => 'weekof', 'descending' => true]], 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'weekof', 'endDateTimeCol' => 'weekof',
                    'onWatchLocalAction' => ['allowApplicationFilter' => ['weeklies' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')]]
                ]]]
            ),
            'objective' => ViewUtils::htmlContent($this, 'SettingObjective', ['atts' => ['edit' => ['style' =>['maxHeight' => '150px', 'overflow' => 'auto']]]]),
            'exercises' => $this->exercises(),
                'protocol' => ViewUtils::htmlContent($this, 'TherapeuticalProtocol', ['atts' => ['edit' => ['style' => ['maxHeight' => '150px', 'overflow' => 'auto']]]]),
            'torespect' => ViewUtils::htmlContent($this, 'QSMToRespect', ['atts' => ['edit' => ['style' => ['maxHeight' => '150px', 'overflow' => 'auto']]]]),
            'qsmchart' => $this->qsmChartDescription()
        ];
        
        $subObjects = [
            'physiopersosessions' => [
                'atts' => [
                    'title' => $this->tr('Sessions'), 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',
                    'endDateTimeCol' => 'startdate',
                    'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
                    'onChangeNotify' => ['calendar' => ['startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'exerciseid' => 'exerciseid', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']],
                    'onDropMap' => ['exercises' => ['fields' => ['name' => 'name', 'startdate' => 'startdate', 'comments' => 'comments', 'rowId' => 'exerciseid', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']]],
                    'sort' => [['property' => 'startdate', 'descending' => true]],
                    'onWatchLocalAction' => [
                        'allowApplicationFilter' => ['physiopersosessions' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')],
                        'collection' => [
                            'calendar' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => 'tWidget.currentView.invalidateLayout();return true;']],
                        ]],
                    'renderCallback' => "if (column.field in  {painduring: true, painafter: true, painnextday: true}){var newColor = {1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'}[rowData[column.field]];domstyle.set(tdCell, 'backgroundColor', newColor);domstyle.set(node, 'backgroundColor', newColor);}",
                    'colsDescription' => [
                        'startdate' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->sessionChangeLocalAction('startdate')]]]]]],
                        'painduring' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->sessionChangeLocalAction('painduring')]]]]]],
                        'painafter' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->sessionChangeLocalAction('painafter')]]]]]],
                        'parentid' => ['atts' => ['storeedit' => ['hidden' => true]]]
                    ],
                    'afterActions' => [
                        'createNewRow' => "this.form.localActions.afterCreateSessionRow(arguments[1][0]);",
                        'updateRow' => "this.form.localActions.afterUpdateSessionRow(arguments[1][0]);",
                        'deleteRow' => "this.form.localActions.afterCreateSessionRow(arguments[1][0]);",
                        'deleteRows' => "this.form.localActions.afterDeleteSessionsRows(arguments[1][0]);",
                    ],
                    'beforeActions' => [
                        //'deleteRows' => "this.form.localActions.beforeSessionDeleteRows(args);",
                        'updateRow' => "this.form.localActions.beforeSessionRowChange(args);",
                        //'deleteRow' => "this.form.localActions.beforeSessionRowChange(args);",
                    ]
                ],
                'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'],
                    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
            ],
            'physiopersodailies' => [
                'atts' => [
                    'title' => $this->tr('Physiopersodailies'), 'allDescendants' => true, 'maxWidth' => '900px',
                    'sort' => [['property' => 'startdate', 'descending' => true]],
                    'renderCallback' => "if (column.field in  {painduring: true, painafter: true, painnextday: true}){var newColor = {1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'}[rowData[column.field]];domstyle.set(tdCell, 'backgroundColor', newColor);domstyle.set(node, 'backgroundColor', newColor);}",
                ],
                'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'],
                    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
            ],
        ];
        $this->customContentAtts = ['edit' => ['widgetsDescription' => ['export' => ['atts' => ['conditionDescription' => "return this.valueOf('id');"]]]]];
        foreach ($customDataWidgets['qsmchart']['atts']['edit']['chartAtts']['type']['cols'] as $col => $description){
            $subObjects['physiopersodailies']['atts']['colsDescription'][$col] = ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => [$col => ['localActionStatus'  => $this->cellChartChangeLocalAction()]]]]]];
        }
        $this->customize($customDataWidgets, $subObjects, ['grid' => ['calendar', 'displayeddate', 'weeklies'], 'get' => ['displayeddate'], 'post' => ['displayeddate']], ['weeklies' => []]);
    }
    function exercises(){
         $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
         return ViewUtils::JsonGrid($this, 'ExercisesList', array_merge(
             ['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true]],
             array_intersect_key($exercisesView->dataWidgets(), ['name' => true, 'stress' => true, 'series' => true, 'repeats' => true, 'extra' => true, 'progression' => true, 'comments' => true])),
             ['atts' => ['edit' => [
                 'sort' => [['property' => 'stress', 'descending' => false]], 'disabled' => true, 'dndParams' => ['copyOnly' => true, 'selfAccept' => false],
                 'onWatchLocalAction' => ['collection' => ['physiopersosessions' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->exercisesLocalAction()]]]]
             ]]]);
     }
     public static function relatedPlanAction(){
         return <<<EOT
Pmg.serverDialog({object: 'physiopersoplans', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: ['name', 'parentid', 'objective', 'exercises', 'protocol', 'torespect']})}}).then(
    function(response){
        var form = sWidget.form, setValueOf = lang.hitch(form, form.setValueOf), item = response.data.value, items;
        delete item.id;
        utils.forEach(item, function(value, widgetName){
            setValueOf(widgetName === 'parentid' ? 'patient' : widgetName, value);
        });
    	return Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItem', query: {parentid: item.parentid, storeatts: JSON.stringify({cols: []})}}).then(
        	function (response){
                setValueOf('acl', {1:{rowId:1,userid: response.data.value.id,permission:2}});        
                Pmg.setFeedback(Pmg.message('actionDone'));
			}
		);	
    }
);
return true;
EOT;
     }
     public static function exercisesLocalAction(){
         return <<<EOT
var form = sWidget.form, exercises = sWidget.collection.fetchSync(), data = [{id: '', name: ''}];
exercises.forEach(function(exercise){
    data.push({id: exercise.idg, name: exercise.name});
});
tWidget.columns.exerciseid.editorArgs.storeArgs.data = data;
if (tWidget.getEditorInstance && tWidget.getEditorInstance('exerciseid')){
    when (tWidget.getEditorInstance('exerciseid'), function(editorInstance){
        editorInstance.store.setData(data);
    });
}
return true;
EOT;
     }
     function OpenEditAction(){
         return <<<EOT
var form = this, grid = form.getWidget('physiopersodailies'), chart = form.getWidget('qsmchart');
chart.plots.day.values = dutils.difference(form.valueOf('fromdate'), form.valueOf('displayeddate'), 'day') + 1;
chart.chart.addPlot('day', chart.plots.day);
try{
    chart.chart.render();
}catch(err){
    console.log('Error rendering chart in localChartAction for widget qsmchart ');
}
require (["tukos/objects/physio/persoTrack/treatments/LocalActions", "tukos/objects/physio/persoTrack/treatments/QSMChart"], function(LocalActions, QSMChart){
    form.localActions = new LocalActions({form: form});
    form.QSMChart = new QSMChart({dailiesStore: grid.store});
    form.QSMChart.setChartValue(form, 'qsmchart');
});
EOT
         ;
     }
     function sessionChangeLocalAction($colName){
         return <<<EOT
return sWidget.form.localActions ? sWidget.form.localActions.sessionCellEditChangeLocalAction(sWidget, tWidget, newValue, oldValue) : true;
EOT;
     }
}
?>
