<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\Widgets;
use TukosLib\Objects\Sports\GoldenCheetah as GC;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait SessionsTracking {
	
    protected $_gcUrl = 'http://localhost:12021/${athlete}?since=${synchrostart}&before=${synchroend}&metadata=${metadata}&metrics=${metrics}';
    
    public function setSessionsTrackingActionWidget($isSportsProgram = true, $sessionsWidget = 'sptsessions', $metricsToInclude = []){
        $tr = $this->view->tr;
        $this->actionWidgets['sessionstracking'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Sessionstracking'), 'allowSave' => true, 'includeWidgets' => ['parentid', 'synchrostart', 'synchroend']]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'sessionstracking';
        $this->actionWidgets['sessionstracking']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => array_merge(
                    $isSportsProgram ? [
                        'eventformurl' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showeventtrackingformurl'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('eventformurl')])),
                        'gcflag' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('gcflag'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('gcflag')])),
                        'formlogo' => Widgets::textBox(Widgets::complete(['label' => $tr('trackingformlogo'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->watchLocalAction('formlogo')])),
                        'formpresentation' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['MobileTextBox', 'default'], $tr)], 'label' => $tr('formpresentation'),
                            'onWatchLocalAction' => $this->watchLocalAction('formpresentation')])),
                        'version' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['V2'], $tr, [false, 'ucfirst', false])], 'label' => $tr('version'),
                            'value' => $this->view->model->defaultSessionsTrackingVersion, 'onWatchLocalAction' => $this->watchLocalAction('version')])),
                     ] : [], [
                        'gcathlete' => Widgets::textBox(Widgets::complete(['title' => $tr('Gcathlete'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->urlChangeLocalAction('gcathlete', $tr)])),
                        'gcsynchrostart' => Widgets::tukosDateBox(['title' => $tr('synchrostart'), 'onWatchLocalAction' => $this->urlChangeLocalAction('gcsynchrostart', $tr, false)]),
                        'gcsynchroend' => Widgets::tukosDateBox(['title' => $tr('synchroend'), 'onWatchLocalAction' => $this->urlChangeLocalAction('gcsynchrostart', $tr, false)]),
                        'gcignoresessionflag' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('gcignoresessionflag'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('gcignoresessionflag')])),
                        'gcmetricstoinclude' => Widgets::multiSelect(Widgets::complete(['title' => $tr('gcmetricstoinclude'), 'options' => GC::metricsOptions($tr, $metricsToInclude), 'style' => ['height' => '150px'],
                            'onWatchLocalAction' =>  $this->urlChangeLocalAction('gcmetricstoinclude', $tr)])),
                        'gcactivitiesmetrics' => Widgets::basicGrid(Widgets::complete(['label' => $tr('gcactivitiesmetrics'), 'allowSelectAll' => true, 'dynamicColumns' => true, 'adjustLastColumn' => false, 'minRowsPerPage' => 500,
                            'colsDescription' => GC::metricsColsDescription($tr, $this->objectName), 'nonGcCols' => GC::nonGcCols(), 'permanentGcOptions' => GC::permanentGcOptions($this->objectName)])),
                        'gclink' => Widgets::htmlContent(['title' => $tr('gclink'), 'readonly' => true]),
                        'gcinput' => Widgets::textArea(Widgets::complete(['title' => $tr('gcinput')])),
                        'gcimport' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('gcimport'), 'onClickAction' => $this->gcimportOnClickAction($sessionsWidget)]],
                        'gcsync' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('gcsync'), 'onClickAction' => $this->gcsyncOnClickAction($sessionsWidget)]],
                        'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
                ]),
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => array_merge(
                        $isSportsProgram ? [
                            'row1' => [
                                'tableAtts' =>['cols' => 5,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
                                'widgets' => ['eventformurl', 'gcflag', 'formlogo', 'formpresentation', 'version'],
                            ]] : [], [
                            'headerRow' => [
                                'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'contents' => [
                                    'title' => [
                                        'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'label' => $tr('gcsynchronization')]
                                    ]
                                ]],
                            'row2' => [
                                'tableAtts' =>['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
                                'widgets' => ['gcathlete', 'gcsynchrostart', 'gcsynchroend', 'gcignoresessionflag'],
                            ],
                            'row3' => [
                                'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'widgetWidths' => ['10%', '90%']],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                        'widgets' => ['gcmetricstoinclude']
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                        'contents' => [
                                            'row1' => [
                                                'tableAtts' => ['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                                'contents' => [
                                                    'col1' => [
                                                        'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                                        'widgets' => ['gclink']
                                                    ],
                                                    'col2' => [
                                                        'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
                                                        'widgets' => ['gcimport', 'gcsync']
                                                    ]
                                                ]
                                            ],
                                            'row2' => [
                                                'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                                'widgets' => ['gcinput'],
                                            ],
                                        ],
                                    ]
                                ],
                            ],
                            'row4' => [
                                'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['gcactivitiesmetrics'],
                            ],
                            'row5' => [
                                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                'widgets' => ['close'],
                            ],
                    ]),
                ],
                'onOpenAction' => $this->sessionsTrackingOnOpenAction($tr),
            ]];
    }
    protected function _urlChangeLocalActionString($tr){
        return <<<EOT
    var synchroStart = pane.valueOf('gcsynchrostart'), synchroEnd = pane.valueOf('gcsynchroend'), gcMetricsToInclude = pane.getWidget('gcmetricstoinclude'), gcLink = pane.getWidget('gclink'),
        gcUrl = string.substitute("{$this->_gcUrl}", {athlete: pane.valueOf('gcathlete'), synchrostart: synchroStart.replace(/-/g, '/'), synchroend: synchroEnd.replace(/-/g, '/'),
            metadata: 'Sport,Workout_Title', metrics: gcMetricsToInclude.get('displayedValue').join(',')});
    gcLink.set('value', string.substitute(Pmg.message('gclinkmessage', "{$this->objectName}"),
        {url: gcUrl, gcmetricstoinclude: '{$tr('gcmetricstoinclude')}', gcinput: '{$tr('gcinput')}', gcimport: '{$tr('gcimport')}', gcactivitiesmetrics: '{$tr('gcactivitiesmetrics')}', gcsync: '{$tr('gcsync')}'}));
EOT;
    }
    protected function sessionsTrackingOnOpenAction($tr){
        $this->view->addToTranslate(['gclinkmessage', 'nomatch', 'newsession', 'synced', 'bicycle', 'swimming', 'running', 'other']);
        return <<<EOT
    var form = this.form, pane = this;
    pane.setWidgets({value: {gcsynchrostart: form.valueOf('synchrostart'), gcsynchroend: form.valueOf('synchroend')}});
    {$this->_urlChangeLocalActionString($tr)};
EOT;
    }
    protected function urlChangeLocalActionString($tr){
        return <<<EOT
    var pane = sWidget.pane;
    {$this->_urlChangeLocalActionString($tr)};
EOT;
    }
    protected function urlChangeLocalAction($widgetName, $tr, $customFlag = true){
        return $this->watchLocalActionTemplate($widgetName, $this->urlChangeLocalActionString($tr) . ($customFlag ? $this->watchLocalActionString() : ''));
    }
    protected function gcimportOnClickAction($sessionsWidget){
        return <<<EOT
  var pane = this.pane, gcMetricsToInclude = pane.getWidget('gcmetricstoinclude'), gcActivitiesMetrics = pane.getWidget('gcactivitiesmetrics'), gcMetricsOptions = gcMetricsToInclude.getOptions(),
      permanentGcOptions = gcActivitiesMetrics.permanentGcOptions, colsDescription = gcActivitiesMetrics.colsDescription,
      gcColumns = {}, form = pane.form, tukosSessions = form.getWidget('$sessionsWidget'), synchroStart = pane.valueOf('gcsynchrostart'), synchroEnd = pane.valueOf('gcsynchroend'),
      tukosSessionsStore = tukosSessions.store, gcCollection = gcActivitiesMetrics.get('collection'), gcDates = [], gcInput = pane.valueOf('gcinput').split(/\\n/g), gcInputLabels = gcInput.shift().split(', '),
      gcInputColNames = utils.flip(lang.mixin(lang.mixin({}, permanentGcOptions), gcMetricsOptions)), data = [], id = 1;
  gcActivitiesMetrics.nonGcCols.forEach(function(name){
      gcColumns[name] = colsDescription[name];
  });
  utils.forEach(permanentGcOptions, function(translatedName, name){
      gcColumns[name] = colsDescription[name];
  });
  gcMetricsToInclude.get('value').forEach(function(name){
      gcColumns[name] = colsDescription[name];
      gcColumns[name].label = gcColumns[name].label.replace(/[_()]/g, ' ');
  });
  gcActivitiesMetrics.set('columns', gcColumns);
  gcInput.forEach(function(activityString){
    if (activityString){
        var row = {id: id}, activity = activityString.split(',');
        activity.forEach(function(value, i){
            var col = gcInputColNames[gcInputLabels[i]], description = colsDescription[col];
            if (col){
                switch (description.gcToTukos){
                    case '/to-':
                        row[col] = value.replace(/[/]/g, '-');
                        break;
                    case 'secondsToTime':
                        row[col] = dutils.secondsToTime(value);
                        break;
                    case 'number':
                        row[col] = Number.parseFloat(value).toFixed(description.formatOptions.places);
                        break;
                    case 'sliceOne':
                        row[col] = value.slice(1, -1);
                        break;
                    case 'sliceOneAndGcToTukos':
                        row[col] = description.gcToTukosOptions.map[value.slice(1, -1)];
                        break;
                    default:
                        row[col] = value;
                }
            }
        });
        utils.array_unique_push(row.date, gcDates);
        data.push(row);
        id += 1;
    }
  });
  gcCollection.setData(data);
  gcDates.forEach(function(date){
    var gcActivities = gcCollection.filter({date: date}).sort('time').fetchSync();
    var tukosSessions = tukosSessionsStore.filter({startdate: date, mode: 'performed'}).sort('sessionid').fetchSync();
    for (var i = 0; i < gcActivities.length; i++){
        if (i < tukosSessions.length){
            gcActivities[i].tukosid = tukosSessions[i].id || Pmg.message('newsession', "{$this->objectName}");
            gcActivities[i].tukosIdProp = tukosSessions[i][tukosSessionsStore.idProperty];
        }else{
            gcActivities[i].tukosid = Pmg.message('nomatch', "{$this->objectName}");
        }
        gcActivities[i].sessionid = i+1;
    }
  });
  gcActivitiesMetrics.set('collection', gcCollection);
  gcActivitiesMetrics.selectAll();
EOT;
    }
    protected function gcsyncOnClickAction($sessionsWidget){
        $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
        return <<<EOT
  var self = this, pane = this.pane, gcActivitiesMetrics = pane.getWidget('gcactivitiesmetrics'), ignoreSessionValue = pane.valueOf('gcignoresessionflag'), selection = gcActivitiesMetrics.get('selection'), gcCollection = gcActivitiesMetrics.get('collection'), gcColumns = gcActivitiesMetrics.columns,
      form = pane.form, sessions = form.getWidget('$sessionsWidget'), sessionsColumns = sessions.columns, sessionsStore = sessions.store, oldestChangedItem;
sessions.isBulkRowAction = true;
utils.forEach(selection, function(isSelected, idProp){
      if (isSelected){
        var gcActivity = gcCollection.getSync(idProp), itemToSync = {}, associatedSessionRow = gcActivity.tukosIdProp ? sessionsStore.getSync(gcActivity.tukosIdProp) : {};
        utils.forEach(gcColumns, function(column, gcName){
            var sessionColName = column.sessionsColName;
            if (!column.hidden && sessionsColumns[sessionColName]){
                var sessionValue = associatedSessionRow[sessionColName], gcValue = gcActivity[gcName], syncValue;
                if (sessionValue !== gcValue){
                    if (ignoreSessionValue){
                        itemToSync[sessionColName] = gcValue;
                    }else{
                        switch(sessionColName){
                            case 'name': case 'sport':
                                syncValue = sessionValue || gcValue; break;
                            case 'duration': case 'timemoving': case 'gch4time': case 'gch5time':
                                syncValue = (!sessionValue || sessionValue === 'T00:00:00') ? gcValue : sessionValue; break;
                            default:
                                syncValue = !Number(sessionValue) ? gcValue : sessionValue;
                        }
                        if (syncValue !== sessionValue){
                            itemToSync[sessionColName] = syncValue;
                        }
                        if (syncValue !== gcValue){
                            gcActivity[gcName] = syncValue;
                        }
                    }
                }
            }
        });
        if (utils.empty(itemToSync)){
                gcActivity.tukosid = gcActivity.tukosid + ' (' + Pmg.message('noneedtosync', "{$this->objectName}") + ')';
        }else{
            itemToSync[sessionsStore.idProperty] = gcActivity.tukosIdProp;
            if (Number(associatedSessionRow.sessionid) !== gcActivity.sessionid){
                itemToSync.sessionid = gcActivity.sessionid;
            }
            if (gcActivity.tukosIdProp){
                sessions.updateRow(itemToSync);
                gcActivity.tukosid = gcActivity.tukosid + ' (' + Pmg.message('synced', "{$this->objectName}") + ')';
            }else{
                itemToSync.mode = 'performed';
                itemToSync.startdate = gcActivity.date;
                itemToSync.acl = {1:{rowId: 1, userid: form.valueOf('updator') || Pmg.get('userid'), permission: 3}, 2:{rowId:2,userid:"$tukosBackOfficeUserId",permission:"3"}};
                var addedItem = sessions.addRow(undefined, itemToSync);
                gcActivity.tukosIdProp = itemToSync[sessionsStore.idProperty];
                gcActivity.tukosid = Pmg.message('newsession', "{$this->objectName}") + ' (' + Pmg.message('synced', "{$this->objectName}") + ')';
            }
            if (!oldestChangedItem || ((addedItem || itemToSync).startdate < oldestChangedItem.startdate)){
                oldestChangedItem = addedItem || itemToSync;
            }
        }
      }
});
gcActivitiesMetrics.set('collection', gcCollection);
if (sessions.tsbCalculator && oldestChangedItem){
    sessions.tsbCalculator.updateRowAction(sessions, oldestChangedItem, true);
    sessions.refresh({keepScrollPosition: true});
    sessions.loadChartUtils.updateCharts(sessions, true);
    sessions.isBulkRowAction = false;
}
EOT;
    }
}
?>