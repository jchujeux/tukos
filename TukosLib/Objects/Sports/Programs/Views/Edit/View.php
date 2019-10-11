<?php

namespace TukosLib\Objects\Sports\Programs\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;

class View extends EditView{

    use LocalActions;
    
	function __construct($actionController){
       parent::__construct($actionController);

        $tr = $this->view->tr;
        $qtr = function($string) use ($tr){
            return $tr($string, 'escapeSQuote');
        };
        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''],
            'contents' => [

            	'row1' => [
                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['id', 'parentid', 'name', 'fromdate', 'duration', 'todate', 'displayeddate', 'googlecalid', 'lastsynctime', 'sportsmanemail', 'synchrostart', 'synchroend', 'synchroweeksbefore', 'synchroweeksafter', 'synchnextmonday', 'questionnairetime']
                ],
            	'row2' => [
                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['33%', '33%', '33%']],      
                    'contents' => [              
                        'col1' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => ['comments', 'loadchart', 'performedloadchart', 'worksheet']],
                        'col2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 'widgets' => [ 'calendar', 'weekloadchart', 'weekperformedloadchart']],
                        'col3' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
                            'widgets' => ['templates',  'warmup', 'mainactivity', 'warmdown'],
                       ],
                    ]
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                    'widgets' => ['sptsessions'],
                ],
                'row4' => [
                     'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                     'widgets' => ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator']
                ],
            ]
        ];

       $this->onOpenAction =
            "var widget = this.getWidget('loadchart');\n" .
            "widget.plots.week.values = dutils.difference(this.valueOf('fromdate'), this.valueOf('displayeddate'), 'week')+1;\n" .
            "widget.chart.addPlot('week', widget.plots.week);\n" .
            "widget.chart.render();\n" .
            $this->view->gridWidgetOpenAction;

       $this->actionWidgets['export']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                   'presentation' => Widgets::storeSelect(Widgets::complete(
                        ['storeArgs' => ['data' => Utl::idsNamesStore($this->view->model->options('presentation'), $this->view->tr)], 'title' => $this->view->tr('presentation'), 'value' => 'perdate',
                         'onWatchLocalAction' => $this->watchLocalAction('presentation'),
                    ])),
                   'contentseparator' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('separator'), 'style' => ['width' => '5em'], 'onWatchLocalAction' =>  $this->watchLocalAction('contentseparator')])),
                   'prefixwarmup' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('warmup'), 'style' => ['width' => '10em'], 'onWatchLocalAction' =>  $this->watchLocalAction('prefixwarmup')])),
                   'prefixmainactivity' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('mainactivity'), 'style' => ['width' => '10em'],  'onWatchLocalAction' => $this->watchLocalAction('prefixmainactivity')])),
                   'prefixwarmdown' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('warmdown'), 'style' => ['width' => '10em'], 'onWatchLocalAction' => $this->watchLocalAction('prefixwarmdown')])),
                   'prefixcomments' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('comments'), 'style' => ['width' => '10em'], 'onWatchLocalAction' =>  $this->watchLocalAction('prefixcomments')])),
                   'duration' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showduration'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('duration')])),
                   'intensity' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showintensity'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('intensity')])),
                   'sport' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showsport'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('sport')])),
                   'sportimage' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showsportimage'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('sportimage')])),
                   'stress' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showmechstress'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('stress')])),
                   'rowintensitycolor' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('rowintensitycolor'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('rowintensitycolor')])),
                   'firstday' => Widgets::tukosDateBox(['title' => $this->view->tr('weekof'), 'onWatchLocalAction' => ['value' => [
                        'weekoftheyear' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.getISOWeekOfYear(newValue)"]],
                        'weekofprogram' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.difference(sWidget.pane.form.valueOf('fromdate'), newValue, 'week') + 1"]],
                        'update' => ['hidden' => ['action' => "return false;" ]],
                    ]]]),
                   'lastday' => Widgets::tukosDateBox(['title' => $this->view->tr('todate'), 'onWatchLocalAction' => ['value' => ['update' => ['hidden' => ['action' => "return false;" ]]]]]),
                   'weekoftheyear' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weekoftheyear'), 'style' => ['width' => '3em']])),
                   'weekofprogram' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weekofprogram'), 'style' => ['width' => '3em']])),
                   'weeksinprogram' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weeksinprogram'), 'style' => ['width' => '3em']])),
                    'update' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('prepare'), 'hidden' => true, 'onClickAction' => 
                        "this.pane.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'updateReport', 'noget' => true]) . "}}, {includeWidgets: ['presentation', 'contentseparator', 'prefixwarmup', 'prefixmainactivity', 'prefixwarmdown', 'prefixcomments', 'duration', 'intensity', 'sport', 'sportimage', 'stress', 'rowintensitycolor', 'firstday', 'lastday', 'weekoftheyear', 'weekofprogram', 'weeksinprogram']}).then(lang.hitch(this, function(){" .
                            "this.pane.previewContent();" .
                            "this.set('hidden', true);" .
                        "}));"  
                    ]],
                    'weeklytable'  => Widgets::editor(Widgets::complete(['title' => $this->view->tr('weeklyprogram'), 'hidden' => true])),
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row8' => [
                            'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'contents' => [
                                'titlerow' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'contents' => ['row1' => [
                                        'tableAtts' => ['label' => $this->view->tr('weeklyprogram')],
                                    ]],
                                ],
                                'row8' => [
                                        'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                                        'widgets' => ['firstday', 'lastday', 'weekoftheyear', 'weekofprogram', 'weeksinprogram'],
                                ],
                               'row9' => [
                                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 100],
                                    'widgets' => ['prefixwarmup', 'prefixmainactivity', 'prefixwarmdown', 'prefixcomments','contentseparator'],
                                ],
                               'row10' => [
                                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 50],
                                    'widgets' => ['presentation', 'duration', 'intensity', 'sport', 'sportimage', 'stress', 'rowintensitycolor'],
                                ],
                                'row11' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['update'],
                                ],
                                'row12' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['weeklytable'],
                                ],
                            ],
                        ],
                    ],
                ],                          
                'onOpenAction' => 
                    "var self = this, date = this.form.getWidget('calendar').get('date'), fromDate = this.form.valueOf('fromdate');" . 
                    "this.watchOnChange = false;" .
                    "return when(this.setWidgets({value: {" .
                        "firstday: dutils.toISO(dutils.getDayOfWeek(1, date))," .
                        "lastday: dutils.toISO(dutils.getDayOfWeek(7, date))," .
                        "weekoftheyear: dutils.getISOWeekOfYear(date)," .
                        "weekofprogram: dutils.difference(fromDate, date, 'week') + 1," .
                        "weeksinprogram: dutils.difference(fromDate, this.form.valueOf('todate'), 'week') + 1," . 
                        "content: ' ' " .
                    "}}), function(){" .
                        "self.watchOnChange = true;" .
                        "return self.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'updateReport', 'noget' => true]) . "}}, {includeWidgets: ['presentation', 'contentseparator', 'prefixwarmup', 'prefixmainactivity', 'prefixwarmdown', 'prefixcomments', 'duration', 'intensity', 'sport', 'sportimage', 'stress', 'rowintensitycolor', 'firstday', 'lastday', 'weekoftheyear', 'weekofprogram', 'weeksinprogram']}).then(function(){"  .
                            "return true;" .
                        "});" .
                    "});" 
            ]
        ];
        $this->actionWidgets['googlesync'] =  ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Googlesync'), 'allowSave' => true,
        	'urlArgs' => ['query' => ['params' => json_encode(['process' => 'googleSynchronize', 'save' => true])]], 'includeWidgets' => ['parentid', 'googlecalid', 'synchrostart', 'synchroend', 'lastsynctime'],
        	'conditionDescription' =>
        		"var form = this.form, googlecalid = form.valueOf('googlecalid');\n" .
        		"if (typeof googlecalid === 'string' && googlecalid.length > 0){\n" .
        			"return true;\n" .
        		"}else{\n" .
                    "Pmg.alert({title: '" . $qtr('needgooglecalid') . "', content: '" . $qtr('youneedtoselectagooglecalid') . "'});\n" .
        			"return false;\n" .
        		"}"
        ]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'googlesync';
		$this->actionWidgets['googleconf'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Googleconf'), 'allowSave' => true]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'googleconf';
		$this->actionWidgets['googleconf']['atts']['dialogDescription'] = [
		    'paneDescription' => [
		        'widgetsDescription' => [
		            'googlecalid' => Widgets::restSelect(Widgets::complete([
		                'title' => $this->view->tr('googlecalid'),	'storeArgs' => ['object' => 'calendars', 'params' => ['getOne' => 'calendarSelect', 'getAll' => 'calendarsSelect']],
		                'onWatchLocalAction' =>  ['value' => ['googlecalid' => ['localActionStatus' => ['action' => "sWidget.pane.form.setValueOf('googlecalid', newValue);sWidget.pane.form.setValueOf('lastsynctime', null);"]],]]
		            ])),
		            'newcalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('newcalendar'), 'onClickAction' =>
		                "var pane = this.pane, targetPane = pane.attachedWidget.form, targetGetWidget = lang.hitch(targetPane, targetPane.getWidget);\n" .
		                "return when(pane.setWidgets({hidden: {newname: false, newacl: false, createcalendar: false, hide: false}, value: {newname: targetGetWidget('name').get('value'), newacl: [{rowId: 1, email: targetGetWidget('sportsmanemail').get('value'), role: 'writer'}]}})," .
		                "function(){\n" .
		                "pane.resize();\n" .
		                "setTimeout(function(){pane.getWidget('newacl').resize();}, 0)\n" .
		                "});\n"
		            ]],
		            'managecalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('managecalendar'), 'onClickAction' =>
		                "var pane = this.pane, getWidget = lang.hitch(pane, pane.getWidget), calId = getWidget('googlecalid').get('value'), label = this.get('label');\n" .
		                "if (typeof calId === 'string' && calId.length > 0){\n" .
		                "this.set('label', Pmg.loading(label));\n" .
		                "pane.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'calendarAcl', 'noget' => true]) . "}}, {includeWidgets: ['googlecalid']}).then(lang.hitch(this, function(response){" .
		                "getWidget('acl').set('value', response.acl);\n" .
		                "this.set('label', label);\n" .
		                "when(pane.setWidgets({hidden: {name: false, acl: false, updateacl: false, deletecalendar: false, hide: false}, value: {name: getWidget('googlecalid').get('value')}})," .
		                "function(){\n" .
		                "pane.resize();\n" .
		                "setTimeout(function(){pane.getWidget('acl').resize();}, 0);\n" .
		                "});}));\n" .
		                "}else{\n" .
		                "Pmg.alert({title: '" . $qtr('needgooglecalid') . "', content: '" . $qtr('youneedtoclicknewcalendar') . "'});\n" .
		                "}"
		            ]],
		            
		            'close' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('close'), 'onClickAction' =>
		                "this.pane.close();\n"
		            ]],
		            'newname' => Widgets::textBox(Widgets::complete(['label' => $tr('newcalendarname'), 'hidden' => true])),
		            'newacl' => Widgets::simpleDgrid(Widgets::complete(
		                ['label' => $tr('accessrules')/*, 'storeType' => 'MemoryTreeObjects'*/, 'hidden' => true, 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '400px'],
		                    'colsDescription' => [
		                        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		                        'email'  => Widgets::description(Widgets::TextBox(['edit' => ['label' => $tr('email')]]), false),
		                        'role' => Widgets::description(Widgets::storeSelect([
		                            'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['reader', 'writer', 'owner'], $tr)], 'label' => $tr('role')]
		                        ]), false),
		                    ]])),
		            'name' => Widgets::textBox(Widgets::complete(['label' => $tr('calendarname'), 'hidden' => true])),
		            'acl' => Widgets::simpleDgrid(Widgets::complete(
		                ['label' => $tr('accessrules'), 'hidden' => true, 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '400px'],
		                    'colsDescription' => [
		                        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		                        'email'  => Widgets::description(Widgets::TextBox(['edit' => ['label' => $tr('email')]]), false),
		                        'role' => Widgets::description(Widgets::storeSelect([
		                            'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['reader', 'writer', 'owner'], $tr)], 'label' => $tr('role')]
		                        ]), false),
		                    ]])),
		            'createcalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('createcalendar'), 'hidden' => true, 'onClickAction' =>
		                "var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');\n" .
		                "this.set('label', Pmg.loading(label));\n" .
		                "pane.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'createCalendar', 'noget' => true]) . "}}, {includeWidgets: ['newname', 'newacl']}).then(lang.hitch(this, function(response){" .
		                "console.log('server action completed');" .
		                "pane.setWidgets({hidden: {newname: true, newacl: true, createcalendar: true, hide: true}, value: {googlecalid: response.googlecalid}});\n" .
		                "this.set('label', label);\n" .
		                "pane.resize();\n" .
		                "}));"
		            ]],
		            'updateacl' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('updateacl'), 'hidden' => true, 'onClickAction' =>
		                "var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');\n" .
		                "this.set('label', Pmg.loading(label));\n" .
		                "pane.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'updateAcl', 'noget' => true]) . "}}, {includeWidgets: ['googlecalid', 'acl']}).then(lang.hitch(this, function(){" .
		                "console.log('server action completed');" .
		                "this.set('label', label);\n" .
		                "pane.resize();\n" .
		                "}));"
		            ]],
		            'deletecalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('deletecalendar'), 'hidden' => true, 'onClickAction' =>
		                "var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');\n" .
		                "this.set('label', Pmg.loading(label));\n" .
		                "pane.serverAction( {action: 'Process', query: {id: true, params: " . json_encode(['process' => 'deleteCalendar', 'noget' => true]) . "}}, {includeWidgets: ['googlecalid']}).then(lang.hitch(this, function(){\n" .
		                "console.log('server action completed');\n" .
		                "pane.setWidgets({hidden: {name: true, acl: true, newacl: true, updateacl: true, deletecalendar: true, hide: true}, value: {newname: '', name: '', newacl: '', acl: '', googlecalid: ''}});\n" .
		                "targetPane.markIfChanged = false;\n" .
		                "targetPane.setWidgets({value: {googlecalid: null, lastsynctime: null}});\n" .
		                "targetPane.markIfChanged = false;\n" .
		                "this.set('label', label);\n" .
		                "pane.resize();\n" .
		                "}));"
		            ]],
		            'hide' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('hide'), 'hidden' => true, 'onClickAction' =>
		                "var pane = this.pane;\n" .
		                "pane.setWidgets({hidden: {name: true, acl: true, deletecalendar: true, newname: true, newacl: true, createcalendar: true, updateacl: true, hide: true}});\n" .
		                "pane.resize();\n"
		            ]],
		        ],
		        'layout' => [
		            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
		            'contents' => [
		                'row1' => [
		                    'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['googlecalid'],
		                ],
		                'row2' => [
		                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['close', 'newcalendar', 'managecalendar'],
		                ],
		                'row4' => [
		                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['newname', 'name'],
		                ],
		                'row5' => [
		                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
		                    'widgets' => ['newacl', 'acl'],
		                ],
		                'row6' => [
		                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['createcalendar', 'updateacl', 'deletecalendar', 'hide'],
		                ],
		            ],
		        ],
		        'onOpenAction' =>
		        "var pane = this, googlecalid = pane.form.getWidget('googlecalid').get('value');\n" .
		        "pane.watchOnChange = false;\n" .
		        "return when(this.setWidgets({value: {googlecalid: googlecalid}}), function(){\n" .
		        "pane.watchOnChange = true;\n" .
		        "});\n"
		    ]];
		$this->actionWidgets['sessionstracking'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Sessionstracking'), 'allowSave' => true, 'includeWidgets' => ['parentid', 'synchrostart', 'synchroend']]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'sessionstracking';
		$this->actionWidgets['sessionstracking']['atts']['dialogDescription'] = [
		    'closeOnBlur' => true,
		    'paneDescription' => [
		        'widgetsDescription' => [
		            'filepath' => Widgets::textBox(Widgets::complete(['label' => $tr('sessionstrackingfilepath'), 'style' => ['width' => '30em'], 'onWatchLocalAction' => Utl::array_merge_recursive_replace($this->watchLocalAction('filepath'), 
		                ['value' => ['downloadperformedsessions' => ['localActionStatus' =>
		                    "var getWidget = lang.hitch(sWidget.form, sWidget.form.getWidget), disabled = newValue ? false : true;" .
		                    "['downloadperformedsessions', 'uploadperformedsessions', 'removeperformedsessions'].forEach(function(name){" .
		                    "    getWidget(name).set('disabled', disabled);" .
		                    "});"
		                ]]])])),
		            'eventformurl' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showeventtrackingformurl'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('eventformurl')])),
		            'formlogo' => Widgets::textBox(Widgets::complete(['label' => $tr('trackingformlogo'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->watchLocalAction('formlogo')])),
		            'formpresentation' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['MobileTextBox', 'default'], $tr)], 'label' => $tr('formpresentation'),
		                'onWatchLocalAction' => $this->watchLocalAction('formpresentation')])),
		            'version' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['V1', 'V2'], $tr, [false, 'ucfirst', false])], 'label' => $tr('version'),
		                'value' => $this->view->model->defaultSessionsTrackingVersion, 'onWatchLocalAction' => $this->watchLocalAction('version')])),
		            'downloadperformedsessions' => $this->sessionsTrackingActionWidgetDescription('downloadPerformedSessions'),
		            'uploadperformedsessions' => $this->sessionsTrackingActionWidgetDescription('uploadPerformedSessions'),
		            'removeperformedsessions' => $this->sessionsTrackingActionWidgetDescription('removePerformedSessions'),
		            'close' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('close'), 'onClickAction' =>
		                "this.pane.close();\n"
		            ]],
		        ],
		        'layout' => [
		            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
		            'contents' => [
		                'row1' => [
		                    'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['filepath'],
		                ],
		                'row2' => [
		                    'tableAtts' =>['cols' =>4,  'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['eventformurl', 'formlogo', 'formpresentation', 'version'],
		                ],
		                'row3' => [
		                    'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['close', 'downloadperformedsessions', 'uploadperformedsessions', 'removeperformedsessions'],
		                ],
		            ],
		        ],
		        'onOpenAction' =>
    		      "var filePath = this.valueOf('filepath'), getWidget = lang.hitch(this, this.getWidget), disabled = filePath ? false : true;" .
			      "['downloadperformedsessions', 'uploadperformedsessions', 'removeperformedsessions'].forEach(function(name){" .
		          "    getWidget(name).set('disabled', disabled);" .
		          "});"
		    ]];
	}
	private function sessionsTrackingActionWidgetDescription($action){
	    return ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr($action), 'onClickAction' =>
	            "var pane = this.pane, parentW = pane.attachedWidget, form = parentW.form, getWidget = lang.hitch(form, form.getWidget), paneValueOf = lang.hitch(pane, pane.valueOf), formValueOf = lang.hitch(form, form.valueOf),". 
                "    label = this.get('label'), urlArgs = parentW.urlArgs;\n" .
	            "this.set('label', Pmg.loading(label));\n" .
	            "form.serverDialog({action: 'Process', query: {id: formValueOf('id'), params: " . json_encode(['process' => $action, 'save' => true]) .
	            "}}, lang.mixin(parentW.valuesToSend, {filepath: paneValueOf('filepath'), version: paneValueOf('version')})," .
	            "  form.get('postElts'), Pmg.message('actionDone')).then(lang.hitch(this, function(response){" .
	            "    console.log('server action completed');" .
	            "    this.set('label', label);\n" .
	            "    pane.close();" .
	        "}));"
	        ]];
	}
}
?>
