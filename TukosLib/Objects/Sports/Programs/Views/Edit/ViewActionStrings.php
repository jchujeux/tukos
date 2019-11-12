<?php
namespace TukosLib\Objects\Sports\Programs\Views\Edit;


trait ViewActionStrings{

  protected function onViewOpenAction(){
        return <<<EOT
    var widget = this.getWidget('loadchart');
        widget.plots.week.values = dutils.difference(this.valueOf('fromdate'), this.valueOf('displayeddate'), 'week')+1;
    widget.chart.addPlot('week', widget.plots.week);
    widget.chart.render();
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
      $object = $this->objectName;
      return <<<EOT
var self = this, html = '', selectedWeeks = this.valueOf('optionalweeks'), tableAtts = 'style="text-align:center; border-collapse: collapse;border-spacing: 0; border: 0;width:100%;"', cellBorderStyle = 'border: solid;border-color: Black;',
    customAtts = this.form.getWidget('calendar').get('customization').items,  backgroundColor = customAtts.style.backgroundColor, imagesAtts = customAtts.img;
    thAtts = 'style="' + cellBorderStyle + '"',  tdAtts = thAtts,   
    buildWeeklyTable = lang.hitch(this, function(mode, title, firstDay, lastDay, weekOfTheYear, weekOfProgram, selectedCols){
    var colsFormat = {session: 'string', duration: 'tHHMMSSToHHMM', intensity: 'string', sport: 'string', sportimage: 'image', stress: 'string', content: 'string'},
        i = 1, sessionsWidget = this.form.getWidget('sptsessions'), filter = new sessionsWidget.store.Filter(), intensityColorFlag = this.valueOf('rowintensitycolor'),
        contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'],
        presentation = self.valueOf('presentation');
    selectedCols.unshift('session');    
    var numberOfCols = selectedCols.length, rowContent = [], rows = [];
    selectedCols.forEach(function(col){
        rowContent.push({tag: 'th', atts: thAtts, content: sessionsWidget.columns[col] ? sessionsWidget.colDisplayedTitle(col) : Pmg.message(col, '$object')});
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
                      '{$tr('trainingplan', 'escapeSQuote')}' + ': ' + this.form.valueOf('name') + '<br>' + title + ' - ' + (presentation === 'persession' 
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
            html += buildWeeklyTable('', '{$tr('plannedsessions')}', firstDay, lastDay, weekOfTheYear, weekOfProgram, self.valueOf('plannedcolstoinclude'));
            break;
        case 'performedthisweek': 
            html += buildWeeklyTable('performed', '{$tr('performedsessions')}', firstDay, lastDay, weekOfTheYear, weekOfProgram, self.valueOf('performedcolstoinclude'));
            break;
        case 'plannedlastweek' : 
            html += buildWeeklyTable('', '{$tr('plannedsessions')}', dutils.formatDate(dojo.date.add(new Date(firstDay), 'week', -1)), dutils.formatDate(dojo.date.add(new Date(lastDay), 'week', -1)),
                        previousWeekOfTheYear, previousWeekOfProgram, self.valueOf('plannedcolstoinclude'));
            break;
        case 'performedlastweek': 
            html += buildWeeklyTable('performed', '{$tr('performedsessions')}', dutils.formatDate(dojo.date.add(new Date(firstDay), 'week', -1)), dutils.formatDate(dojo.date.add(new Date(lastDay), 'week', -1)),
                        previousWeekOfTheYear, previousWeekOfProgram, self.valueOf('performedcolstoinclude'));
            break;
    }
});
console.log('generated html: ' + html);
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
	}));"
EOT;
  }
  protected function googleSyncUpdateAclOnClickAction(){
      return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, paneGetWidget = lang.hitch(pane, pane.getWidget), targetGetValue = lang.hitch(targetPane, targetPane.getWidget), label = this.get('label');
	this.set('label', Pmg.loading(label));
	pane.serverAction( {action: 'Process', query: {id: true, params: {process: 'updateAcl', noget: true}}, {includeWidgets: ['googlecalid', 'acl']}).then(lang.hitch(this, function(){
		console.log('server action completed');
		this.set('label', label);
		pane.resize();
	}));
EOT;
  }
  protected function googleSyncDeleteCalendarOnClickAction(){
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
  protected function googleSyncHideOnClickAction(){
    return <<<EOT
      var pane = this.pane;
      pane.setWidgets({hidden: {name: true, acl: true, deletecalendar: true, newname: true, newacl: true, createcalendar: true, updateacl: true, hide: true}});
      pane.resize();
EOT;
  }
  protected function googleSyncOnOpenAction(){
      return <<<EOT
    var pane = this, googlecalid = pane.form.getWidget('googlecalid').get('value');
	pane.watchOnChange = false;
	return when(this.setWidgets({value: {googlecalid: googlecalid}}), function(){
		pane.watchOnChange = true;
	});
EOT;
  }
  protected function sessionsTrackingOnOpenAction(){
      return <<<EOT
    var filePath = this.valueOf('filepath'), getWidget = lang.hitch(this, this.getWidget), disabled = filePath ? false : true;
	['downloadperformedsessions', 'uploadperformedsessions', 'removeperformedsessions'].forEach(function(name){
	    getWidget(name).set('disabled', disabled);
	});
EOT;
  }
  protected function sessionsTrackingActionButtonsOnClickAction($action){
      return <<<EOT
	var pane = this.pane, parentW = pane.attachedWidget, form = parentW.form, getWidget = lang.hitch(form, form.getWidget), paneValueOf = lang.hitch(pane, pane.valueOf), formValueOf = lang.hitch(form, form.valueOf), 
        label = this.get('label'), urlArgs = parentW.urlArgs;
	this.set('label', Pmg.loading(label));
	form.serverDialog({action: 'Process', query: {id: formValueOf('id'), params: {process: '$action', save: true}}}, 
            lang.mixin(parentW.valuesToSend, {filepath: paneValueOf('filepath'), version: paneValueOf('version')}), form.get('postElts'), Pmg.message('actionDone')).then(lang.hitch(this, function(response){
	    console.log('server action completed');
	    this.set('label', label);
	    pane.close();
	}));
EOT;
  }
}
?>
