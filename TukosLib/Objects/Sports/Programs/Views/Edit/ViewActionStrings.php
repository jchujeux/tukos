<?php
namespace TukosLib\Objects\Sports\Programs\Views\Edit;


trait ViewActionStrings{

    protected $_gcUrl = 'http://localhost:12021/${athlete}?since=${synchrostart}&before=${synchroend}&metadata=${metadata}&metrics=${metrics}';
    
    protected function onViewOpenAction(){
        return <<<EOT
['loadchart', 'performedloadchart'].forEach(lang.hitch(this, function(widgetName){    
    var widget = this.getWidget(widgetName);
        widget.plots.week.values = dutils.difference(dutils.getDayOfWeek(1, new Date(this.valueOf('fromdate'))), this.valueOf('displayeddate'), 'week')+1;
    widget.chart.addPlot('week', widget.plots.week);
	try{
    widget.chart.render();
    }catch(err){
        console.log('Error rendering chart in onViewOpenAction for widget: ' + widget.widgetName);
    }
}));
EOT;
  }
  protected function paneUpdateOnClickAction(){
        return <<<EOT
    this.form.customContentCallbackFunction();
    this.pane.previewContent();
EOT;
  }
  protected function exportPaneOnOpenAction(){
      return <<<EOT
    var self = this, date = this.form.getWidget('calendar').get('date'), fromDate = this.form.valueOf('fromdate'); 
    if (!this.customContentCallbackFunction){
        this.customContentCallbackFunction = this.myEval(this.customContentCallback, '');
    }
    this.watchOnChange = false;
    return when(this.setWidgets({value: {
        firstday: dutils.toISO(dutils.getDayOfWeek(1, date)),
        lastday: dutils.toISO(dutils.getDayOfWeek(7, date)),
        weekoftheyear: dutils.getISOWeekOfYear(date),
        weekofprogram: dutils.difference(fromDate, date, 'week'),
        weeksinprogram: dutils.difference(fromDate, this.form.valueOf('todate'), 'week') + 1, content: ' '}}), function(){
            self.watchOnChange = true;
            self.customContentCallbackFunction();
            var dfd = new Deferred();
            dfd.resolve();
            return dfd.promise;    
        });
EOT;
  }
  protected function exportCustomContent($tr){
      $this->view->addToTranslate(['session', 'sportimage', 'content']);
      return <<<EOT
    var self = this, form = this.form, html = '', selectedWeeks = this.getWidget('optionalweeks').get('value'), 
        tableAtts = 'style="text-align:center; border-collapse: collapse;border-spacing: 0; border: 0;width:100%;"', cellBorderStyle = 'border: solid;border-color: Black;',
        customAtts = form.getWidget('calendar').get('customization').items,  backgroundColor = customAtts.style.backgroundColor, imagesAtts = customAtts.img,
        thAtts = 'style="' + cellBorderStyle + '"',  tdAtts = thAtts,   
        buildWeeklyTable = lang.hitch(this, function(mode, title, firstDay, lastDay, weekOfTheYear, weekOfProgram, selectedCols){
    var colsFormat = {session: 'string', duration: 'tHHMMSSToHHMM', intensity: 'string', sport: 'string', sportimage: 'image', stress: 'string', content: 'string'},
        i = 1, sessionsWidget = form.getWidget('sptsessions'), filter = new sessionsWidget.store.Filter(), intensityColorFlag = this.valueOf('rowintensitycolor'),
        contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'],
        presentation = self.valueOf('presentation');
    selectedCols.unshift('session');    
    var numberOfCols = selectedCols.length, rowContent = [], rows = [];
    selectedCols.forEach(function(col){
        rowContent.push({tag: 'th', atts: thAtts, content: sessionsWidget.columns[col] ? sessionsWidget.colDisplayedTitle(col) : Pmg.message(col, 'sptprograms')});
    });
    rows.push({tag: 'tr', content: rowContent});
    sessionsWidget.store.filter(filter.gte('startdate', firstDay).lte('startdate', lastDay)[mode === 'performed' ? 'eq' : 'ne']('mode', 'performed').gt('duration', 'T00:00:00')).sort('startdate').forEach(function(session){
        var intensityColorAtt = 'style="background-color: ' + ((intensityColorFlag === 'on' && session.intensity) ? backgroundColor.map[session.intensity] : backgroundColor.defaultValue)  + ';"';
        rowContent = [];        
        selectedCols.forEach(function(col){
            var colContent, colTdAtts = tdAtts;            
            switch (col){
                case 'session':
                    colContent = utils.capitalize(Pmg.message(dutils.dateToDayName(new Date(session.startdate)))) + (presentation === 'persession' ? '' : (' ' + utils.transform(session.startdate, 'date', {selector: 'date', datePattern: 'd'})));
                    break;
                case 'duration':
                    colContent = session.sport === 'rest' ? '' : sessionsWidget.colDisplayedValue(session.duration, 'duration');
                    break;
                case 'content':
                    var activeContent = [];
                    contentCols.forEach(function(col){
                        if (session[col]){
                            activeContent.push(self.valueOf('prefix' + col) + session[col]);
                        }
                    });
                    colContent = activeContent.join(self.valueOf('contentseparator'));
                    break;
                case 'sportimage':
                    colContent = session.sport ? utils.transform(imagesAtts.imagesDir + imagesAtts.map[session.sport], 'image') : session.sportImage;
                    break;
                case 'coachcomments':
                    colTdAtts = 'style="' + cellBorderStyle + 'background-color: LightGrey; font-style: italic;"';
                default:
                    colContent = sessionsWidget.colDisplayedValue(session[col], col);
            }
            rowContent.push({tag: 'td', atts: colTdAtts, content: colContent});
        });
        rows.push({tag: 'tr', atts: intensityColorAtt, content: rowContent});
        i += 1;
    });
    if (mode === 'performed'){
        rows.push({tag: 'tr', content: 
            {tag: 'td', atts: 'colspan=' + numberOfCols + ' style="background-color: #99ccff; color: White; font-size: large; font-weight: bold; ' + cellBorderStyle + '"', content: '<br>'}
        });
        rowContent = []; weeklyRows = [];
        rowContent.push({tag: 'th', atts: 'style="border-right: solid black; border-bottom: solid Black; width:50%;"', content: sessionsWidget.colDisplayedTitle('athleteweeklyfeeling')});
        rowContent.push({tag: 'th', atts: 'style="border: 0; border-bottom: solid Black; width:50%;"', content: sessionsWidget.colDisplayedTitle('coachweeklycomments')});
        weeklyRows.push({tag: 'tr', content: rowContent});
        sessionsWidget.store.filter(filter.eq('startdate', firstDay).eq('mode', 'performed')).forEach(function(mondaySession){
            rowContent = [];
            rowContent.push({tag: 'td', atts: 'style="border-right: solid black;background-color: ' + backgroundColor.defaultValue + ';"', content: sessionsWidget.colDisplayedValue(mondaySession.athleteweeklyfeeling, 'athleteweeklyfeeling')});
            rowContent.push({tag: 'td', atts: 'style="border: 0;background-color: LightGrey;font-style: italic"', content: sessionsWidget.colDisplayedValue(mondaySession.coachweeklycomments, 'coachweeklycomments')});
            weeklyRows.push({tag: 'tr', content: rowContent});
        });
        rows.push({tag: 'tr', content: {tag: 'td', atts: 'style="border: solid Black;"' + ' colspan=' + numberOfCols, content: {tag: 'table', atts: 'style="text-align:center; border-collapse: collapse;border-spacing: 0; border: 0; margin: 0; width:100%;"', content: weeklyRows}}});
    }
    return hiutils.buildHtml([
        '<br>', 
        {tag: 'table', atts: tableAtts, content: [
            {tag: 'tr', content: {tag: 'td', atts: 'style="background-color: #3a3a3a; color: White; font-size: large; font-weight: bold; ' + cellBorderStyle + '" colspan=' + numberOfCols, content:
                {tag: 'table', atts: tableAtts, content: {tag: 'tr', content: [
                    {tag: 'td', atts: 'style="width: 10%;"', content: utils.transform(imagesAtts.imagesDir + 'TDSLogoBlackH64.jpg', 'image')},
                    {tag: 'td', atts: 'style="text-align:center; color: White; font-size: large; font-weight: bold; width: 90%"', content: 
                      '{$tr('trainingplan', 'escapeSQuote')}' + ': ' + form.valueOf('name') + '<br>' + title + ' - ' + (presentation === 'persession' 
                        ? ''
                        : ('{$tr('week')} ' + weekOfTheYear + ': {$tr('fromdate')} ' + utils.transform(firstDay, 'date') + ' {$tr('todate')} ' + utils.transform(lastDay, 'date') + ' (')) + 
                     '{$tr('week')} ' + weekOfProgram + ' / ' + this.valueOf('weeksinprogram') + (presentation === 'persession' ? '' : ')')}
                ]}}
            }},
            {tag: 'tr', content: {tag: 'td', atts: 'style="background-color: #99ccff; color: White; font-size: large; font-weight: bold; ' + cellBorderStyle + '" colspan=' + numberOfCols, content: '<br>'}},
            rows,
        ]},
        '<br>'
    ]);
});
selectedWeeks.forEach(function(selectedWeek){
    var firstDay = self.valueOf('firstday'), lastDay = self.valueOf('lastday'), weekOfTheYear = self.valueOf('weekoftheyear'), weekOfProgram = self.valueOf('weekofprogram'),
        previousWeekOfTheYear = parseInt(weekOfTheYear) - 1, previousWeekOfProgram = parseInt(weekOfProgram) - 1;
    switch(selectedWeek){
        case 'plannedthisweek' : 
            html += buildWeeklyTable('', '{$tr('plannedsessions')}', firstDay, lastDay, weekOfTheYear, weekOfProgram, self.getWidget('plannedcolstoinclude').get('value'));
            break;
        case 'performedthisweek': 
            html += buildWeeklyTable('performed', '{$tr('performedsessions')}', firstDay, lastDay, weekOfTheYear, weekOfProgram, self.getWidget('performedcolstoinclude').get('value'));
            break;
        case 'plannedlastweek' : 
            html += buildWeeklyTable('', '{$tr('plannedsessions')}', dutils.formatDate(dojo.date.add(new Date(firstDay), 'week', -1)), dutils.formatDate(dojo.date.add(new Date(lastDay), 'week', -1)),
                        previousWeekOfTheYear, previousWeekOfProgram, self.getWidget('plannedcolstoinclude').get('value'));
            break;
        case 'performedlastweek': 
            html += buildWeeklyTable('performed', '{$tr('performedsessions')}', dutils.formatDate(dojo.date.add(new Date(firstDay), 'week', -1)), dutils.formatDate(dojo.date.add(new Date(lastDay), 'week', -1)),
                        previousWeekOfTheYear, previousWeekOfProgram, self.getWidget('performedcolstoinclude').get('value'));
            break;
    }
});
//console.log('generated html: ' + html);
this.getWidget('weeklytable').set('value', html);
EOT;
  }
  protected function googleSyncConditionDescription($needGoogleCalId, $youNeedToSelectAGoogleCalId){
    return <<<EOT
    var form = this.form, googlecalid = form.valueOf('googlecalid');
    if (typeof googlecalid === 'string' && googlecalid.length > 0){
        return true;
    }else{
        Pmg.alert({title: '$needGoogleCalId', content: '$youNeedToSelectAGoogleCalId'});
        return false;
    }
EOT;
  }
  protected function googleConfNewCalendarOnClickAction (){
    return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, targetGetWidget = lang.hitch(targetPane, targetPane.getWidget);
	return when(pane.setWidgets({hidden: {newname: false, newacl: false, createcalendar: false, hide: false}, value: {newname: targetGetWidget('name').get('value'), newacl: [{rowId: 1, email: targetGetWidget('sportsmanemail').get('value'),
            role: 'writer'}]}}), function(){
		pane.resize();
		setTimeout(function(){pane.getWidget('newacl').resize();}, 0)
	});
EOT;
  }
  protected function googleConfManageCalendarOnClickAction($needGoogleCalId, $youNeedToClickNewCalendar){
      return <<<EOT
	var pane = this.pane, getWidget = lang.hitch(pane, pane.getWidget), calId = getWidget('googlecalid').get('value'), label = this.get('label');
	if (typeof calId === 'string' && calId.length > 0){
		this.set('label', Pmg.loading(label));
		pane.serverAction({action: 'Process', query: {id: true, params: {process: 'calendarAcl', noget: true}}}, {includeWidgets: ['googlecalid']}).then(lang.hitch(this, function(response){
		    getWidget('acl').set('value', response.acl);
		    this.set('label', label);
		    when(pane.setWidgets({hidden: {name: false, acl: false, updateacl: false, deletecalendar: false, hide: false}, value: {name: getWidget('googlecalid').get('value')}}), function(){
		        pane.resize();
		        setTimeout(function(){pane.getWidget('acl').resize();}, 0);
	    });}));
	}else{
		Pmg.alert({title: '$needGoogleCalId', content: '$youNeedToClickNewCalendar'});
	}
EOT;
  }
  protected function googleConfCreateCalendarOnClickAction(){
      return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');
	this.set('label', Pmg.loading(label));
	pane.serverAction( {action: 'Process', query: {id: true, params: {process: 'createCalendar', noget: true}}}, {includeWidgets: ['newname', 'newacl']}).then(lang.hitch(this, function(response){
		console.log('server action completed');
		pane.setWidgets({hidden: {newname: true, newacl: true, createcalendar: true, hide: true}, value: {googlecalid: response.googlecalid}});
		this.set('label', label);
		pane.resize();
	}));
EOT;
  }
  protected function googleConfUpdateAclOnClickAction(){
      return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');
	this.set('label', Pmg.loading(label));
	pane.serverAction( {action: 'Process', query: {id: true, params: {process: 'updateAcl', noget: true}}}, {includeWidgets: ['googlecalid', 'acl']}).then(lang.hitch(this, function(response){
		console.log('server action completed');
		pane.getWidget('acl').set('value', response.acl);
		this.set('label', label);
		pane.resize();
		setTimeout(function(){pane.getWidget('acl').resize();}, 0);
	}));
EOT;
  }
  protected function googleConfDeleteCalendarOnClickAction(){
      return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');
	this.set('label', Pmg.loading(label));
	pane.serverAction( {action: 'Process', query: {id: true, params: {process: 'deleteCalendar', noget: true}}, {includeWidgets: ['googlecalid']}).then(lang.hitch(this, function(){
	    console.log('server action completed');
		pane.setWidgets({hidden: {name: true, acl: true, newacl: true, updateacl: true, deletecalendar: true, hide: true}, value: {newname: '', name: '', newacl: '', acl: '', googlecalid: ''}});
		targetPane.markIfChanged = false;
		targetPane.setWidgets({value: {googlecalid: null, lastsynctime: null}});
		targetPane.markIfChanged = false;
		this.set('label', label);
		pane.resize();
	}));
EOT;
  }
  protected function googleConfHideOnClickAction(){
    return <<<EOT
      var pane = this.pane;
      pane.setWidgets({hidden: {name: true, acl: true, deletecalendar: true, newname: true, newacl: true, createcalendar: true, updateacl: true, hide: true}});
      pane.resize();
EOT;
  }
  protected function googleConfOnOpenAction(){
      return <<<EOT
    var pane = this, googlecalid = pane.form.getWidget('googlecalid').get('value');
	pane.watchOnChange = false;
	return when(this.setWidgets({value: {googlecalid: googlecalid}}), function(){
		pane.watchOnChange = true;
	});
EOT;
  }
  protected function _urlChangeLocalActionString($tr){
      return <<<EOT
    var synchroStart = pane.valueOf('gcsynchrostart'), synchroEnd = pane.valueOf('gcsynchroend'), gcMetricsToInclude = pane.getWidget('gcmetricstoinclude'), gcLink = pane.getWidget('gclink'),
        gcUrl = string.substitute("{$this->_gcUrl}", {athlete: pane.valueOf('gcathlete'), synchrostart: synchroStart.replace(/-/g, '/'), synchroend: synchroEnd.replace(/-/g, '/'),
            metadata: 'Sport,Workout_Title', metrics: gcMetricsToInclude.get('displayedValue').join(',')});
    gcLink.set('value', string.substitute(Pmg.message('gclinkmessage', 'sptprograms'), 
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
  protected function gcimportOnClickAction($tr){
      return <<<EOT
  var pane = this.pane, gcMetricsToInclude = pane.getWidget('gcmetricstoinclude'), gcActivitiesMetrics = pane.getWidget('gcactivitiesmetrics'), gcMetricsOptions = gcMetricsToInclude.getOptions(),
      permanentGcOptions = gcActivitiesMetrics.permanentGcOptions, colsDescription = gcActivitiesMetrics.colsDescription,
      gcColumns = {}, form = pane.form, tukosSessions = form.getWidget('sptsessions'), synchroStart = pane.valueOf('gcsynchrostart'), synchroEnd = pane.valueOf('gcsynchroend'),
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
            gcActivities[i].tukosid = tukosSessions[i].id || Pmg.message('newsession', 'sptprograms');
            gcActivities[i].tukosIdProp = tukosSessions[i][tukosSessionsStore.idProperty];
        }else{
            gcActivities[i].tukosid = Pmg.message('nomatch', 'sptprograms');
        }
        gcActivities[i].sessionid = i+1;
    }
  });
  gcActivitiesMetrics.set('collection', gcCollection);
  gcActivitiesMetrics.selectAll();
EOT;
  }
  protected function gcsyncOnClickAction(){
      return <<<EOT
  var self = this, pane = this.pane, gcActivitiesMetrics = pane.getWidget('gcactivitiesmetrics'), selection = gcActivitiesMetrics.get('selection'), gcCollection = gcActivitiesMetrics.get('collection'), gcColumns = gcActivitiesMetrics.columns, 
      form = pane.form, sessions = form.getWidget('sptsessions'), sessionsStore = sessions.store, oldestChangedItem;
sessions.isBulkRowAction = true;  
utils.forEach(selection, function(isSelected, idProp){
      if (isSelected){
        var gcActivity = gcCollection.getSync(idProp), itemToSync = {}, associatedSessionRow = gcActivity.tukosIdProp ? sessionsStore.getSync(gcActivity.tukosIdProp) : {};
        utils.forEach(gcColumns, function(column, gcName){
            var sessionColName = column.sessionsColName;
            if (sessionColName && (!utils.in_array(sessionColName, ['name', 'sport']) || !associatedSessionRow[sessionColName])){
                itemToSync[sessionColName] = gcActivity[gcName];
            }
        });
        if (!utils.empty(itemToSync)){
            itemToSync[sessionsStore.idProperty] = gcActivity.tukosIdProp;
            itemToSync.sessionid = gcActivity.sessionid;
        }
        if (gcActivity.tukosIdProp){
            sessions.updateRow(itemToSync);
            gcActivity.tukosid = gcActivity.tukosid + ' (' + Pmg.message('synced', 'sptprograms') + ')';
        }else{
            itemToSync.mode = 'performed';
            itemToSync.startdate = gcActivity.date;
            var addedItem = sessions.addRow(undefined, itemToSync);
            gcActivity.tukosIdProp = itemToSync[sessionsStore.idProperty];
            gcActivity.tukosid = Pmg.message('newsession', 'sptprograms') + ' (' + Pmg.message('synced', 'sptprograms') + ')';
        }
        if (!oldestChangedItem || ((addedItem || itemToSync).startdate < oldestChangedItem.startdate)){
            oldestChangedItem = addedItem || itemToSync;
        }
      }
});
gcActivitiesMetrics.set('collection', gcCollection);
if (oldestChangedItem){
    sessions.tsbCalculator.updateRowAction(sessions, oldestChangedItem, true);
    sessions.refresh({keepScrollPosition: true});
    sessions.loadChartUtils.updateCharts(sessions, true);
    sessions.isBulkRowAction = false;
}
EOT;
  }
}
?>
