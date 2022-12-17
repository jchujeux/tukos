<?php
namespace TukosLib\Objects\Sports\Programs\Views\Edit;

use TukosLib\TukosFramework as Tfk;

trait ViewActionStrings{

    protected $_gcUrl = 'http://localhost:12021/${athlete}?since=${synchrostart}&before=${synchroend}&metadata=${metadata}&metrics=${metrics}';
    
    protected function onViewOpenAction(){
        return <<<EOT
['loadchart', 'performedloadchart'].forEach(lang.hitch(this, function(widgetName){    
    var widget = this.getWidget(widgetName);
        widget.plots.week.values = dutils.difference(dutils.getDayOfWeek(1, new Date(this.valueOf('displayfromdate'))), this.valueOf('displayeddate'), 'week')+1;
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
    var self = this, form = this.form, date = form.getWidget('calendar').get('date'), fromDate = form.valueOf('fromdate'); 
    if (!this.customContentCallbackFunction){
        this.customContentCallbackFunction = this.myEval(this.customContentCallback, '');
    }
    this.watchOnChange = false;
    return when(this.setWidgets({value: {
        to: this.valueOf('to') || form.valueOf('sportsmanemail'),
        cc: this.valueOf('cc') || form.valueOf('coachemail'),
        from: this.valueOf('from') || 18,
        subject: this.valueOf('subject') || form.valueOf('name'),
        firstday: dutils.toISO(dutils.getDayOfWeek(1, date)),
        lastday: dutils.toISO(dutils.getDayOfWeek(7, date)),
        weekoftheyear: dutils.getISOWeekOfYear(date),
        weekofprogram: dutils.difference(fromDate, date, 'week'),
        weeksinprogram: dutils.difference(fromDate, form.valueOf('todate'), 'week') + 1, content: ' '}}), function(){
            self.watchOnChange = true;
            return self.customContentCallbackFunction();
        });
EOT;
  }
  protected function exportCustomContent($tr){
      $this->view->addToTranslate(['session', 'sportimage', 'content']);
      return <<<EOT
/*
    Pmg.serverDialog({object: 'sptathletes', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: ['id']})}}).then(
        function(response){
        	return Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItem', query: {parentid: response.data.value.id, storeatts: JSON.stringify({cols: []})}}).then(
            	function (response){
                    acl.set('value', '');
                    if (response.data.value.id){
                        acl.addRow(null, {rowId:1,userid: response.data.value.id,permission:"2"});
                    }else{
                        Pmg.setFeedback(Pmg.message('sportsmanhasnouserassociatednoacl', 'sptprograms'));
                    }
    			}
    		);
        }
    );
*/
var self = this, form = this.form, coachOrganization = form.valueOf('coachorganization');
return Pmg.serverDialog({object: 'organizations', view: 'Edit', action: 'GetItem', query: {id: coachOrganization, storeatts: JSON.stringify({cols: ['logo']})}}).then(function(response){
     var logo = response.data.value.logo, html = '', selectedWeeks = self.getWidget('optionalweeks').get('value'), 
        tableAtts = 'style="text-align:center; border-collapse: collapse;border-spacing: 0; border: 0;width:100%;"', cellBorderStyle = 'border: solid;border-color: Black;',
        customAtts = form.getWidget('calendar').get('customization').items,  backgroundColor = customAtts.style.backgroundColor, imagesAtts = customAtts.img,
        thAtts = 'style="' + cellBorderStyle + '"',  tdAtts = thAtts,   
        buildWeeklyTable = lang.hitch(this, function(mode, title, firstDay, lastDay, weekOfTheYear, weekOfProgram, selectedCols){
        var colsFormat = {session: 'string', duration: 'tHHMMSSToHHMM', intensity: 'string', sport: 'string', sportimage: 'image', stress: 'string', content: 'string'}, i = 1, 
            sessionsWidget = form.getWidget('sptsessions'), sessionsFilter = new sessionsWidget.store.Filter(), weekliesWidget = form.getWidget('weeklies'), weekliesFilter = new weekliesWidget.store.Filter(), intensityColorFlag = this.valueOf('rowintensitycolor'),
            contentCols = ['warmup', 'mainactivity', 'warmdown', 'comments'],
            presentation = self.valueOf('presentation');
        selectedCols.unshift('session');    
        var numberOfCols = selectedCols.length, rowContent = [], rows = [];
        selectedCols.forEach(function(col){
            rowContent.push({tag: 'th', atts: thAtts, content: sessionsWidget.columns[col] ? sessionsWidget.colDisplayedTitle(col) : Pmg.message(col, 'sptprograms')});
        });
        rows.push({tag: 'tr', content: rowContent});
        sessionsWidget.store.filter(sessionsFilter.gte('startdate', firstDay).lte('startdate', lastDay)[mode === 'performed' ? 'eq' : 'ne']('mode', 'performed')).sort('startdate').forEach(function(session){
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
            rowContent.push({tag: 'th', atts: 'style="border-right: solid black; border-bottom: solid Black; width:50%;"', content: weekliesWidget.colDisplayedTitle('athleteweeklyfeeling')});
            rowContent.push({tag: 'th', atts: 'style="border: 0; border-bottom: solid Black; width:50%;"', content: weekliesWidget.colDisplayedTitle('coachweeklycomments')});
            weeklyRows.push({tag: 'tr', content: rowContent});
            weekliesWidget.store.filter(weekliesFilter.gte('weekof', firstDay).lte('weekof', lastDay)).forEach(function(weekly){
                rowContent = [];
                rowContent.push({tag: 'td', atts: 'style="border-right: solid black;background-color: ' + backgroundColor.defaultValue + ';"', content: weekliesWidget.colDisplayedValue(weekly.athleteweeklyfeeling, 'athleteweeklyfeeling')});
                rowContent.push({tag: 'td', atts: 'style="border: 0;background-color: LightGrey;font-style: italic"', content: weekliesWidget.colDisplayedValue(weekly.coachweeklycomments, 'coachweeklycomments')});
                weeklyRows.push({tag: 'tr', content: rowContent});
            });
            rows.push({tag: 'tr', content: {tag: 'td', atts: 'style="border: solid Black;"' + ' colspan=' + numberOfCols, content: {tag: 'table', atts: 'style="text-align:center; border-collapse: collapse;border-spacing: 0; border: 0; margin: 0; width:100%;"', content: weeklyRows}}});
        }
        return hiutils.buildHtml([
            '<br>', 
            {tag: 'table', atts: tableAtts, content: [
                {tag: 'tr', content: {tag: 'td', atts: 'style="background-color: black; color: White; font-size: large; font-weight: bold; ' + cellBorderStyle + '" colspan=' + numberOfCols, content:
                    {tag: 'table', atts: tableAtts, content: {tag: 'tr', content: [
                        {tag: 'td', atts: 'style="width: 10%;"', content: logo},//utils.transform(logo, 'image')},
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
    self.getWidget('weeklytable').set('value', html);
});
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
  protected function googleConfCalIdOnWatchAction(){
      return <<<EOT
var pane = sWidget.pane, form = pane.form;
if (newValue){
    pane.setWidgets({hidden: {managecalendar: false, deletecalendar: false, createcalendar: true, newname: true, newacl: true}});
}else{
    pane.setWidgets({hidden: {name: true, acl: true, managecalendar: true, deletecalendar: true, newname: true, newacl: true, createcalendar: true}});
}
form.setWidgets({value: {googlecalid: newValue, lastsynctime: null}});
EOT;
  }
  protected function googleConfNewCalendarOnClickAction (){
    return <<<EOT
	var pane = this.pane, targetPane = pane.attachedWidget.form, targetGetWidget = lang.hitch(targetPane, targetPane.getWidget), newAcl = pane.getWidget('newacl');
	return when (pane.emptyWidgets(['newname', 'newacl']), function(){
        when(pane.setWidgets({hidden: {newname: false, newacl: false, name: true, acl: true, createcalendar: false, hide: false, managecalendar: true, updateacl: true, deletecalendar: true}, value: {googlecalid: '', name: '', newname: targetGetWidget('name').get('value'), newacl: []}}), function(){
            var sportsManEmail = targetGetWidget('sportsmanemail').get('value'), coachEmail = targetGetWidget('coachemail').get('value');
            if (sportsManEmail == '' || 'coachEmail' == ''){
                Pmg.alert({title: Pmg.message('missinginformation'), content: Pmg.message('needcoach and athlete emails')});
            }else{
                newAcl.addRow(undefined, {rowId: 1, email: coachEmail, role: 'owner'});
                if (sportsManEmail !== coachEmail){
                    newAcl.addRow(undefined, {rowId: 2, email: sportsManEmail, role: 'reader'});
                }
                pane.resize();
		      setTimeout(function(){pane.getWidget('newacl').resize();}, 0);
            }
        });
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
		    when(pane.setWidgets({hidden: {name: false, acl: false, newname: true, newacl: true, updateacl: false, deletecalendar: false, hide: false}, value: {name: getWidget('googlecalid').get('value')}}), function(){
		          pane.resize();
		          setTimeout(function(){pane.getWidget('acl').resize();}, 0);
	           });
        }));
	}else{
		Pmg.alert({title: '$needGoogleCalId', content: '$youNeedToClickNewCalendar'});
	}
EOT;
  }
  protected function googleConfCreateCalendarOnClickAction(){
      return <<<EOT
	var pane = this.pane, label = this.get('label'), newname = pane.valueOf('newname');
if (newname){
    this.set('label', Pmg.loading(label));
	pane.serverAction( {action: 'Process', query: {id: true, params: {process: 'createCalendar', noget: true}}}, {includeWidgets: ['newname', 'newacl']}).then(lang.hitch(this, function(response){
		console.log('server action completed');
		pane.setWidgets({hidden: {newname: true, newacl: true, managecalendar: false, createcalendar: true, hide: true}, value: {googlecalid: response.googlecalid}});
		this.set('label', label);
		pane.resize();
	}));
}else{
    Pmg.setFeedback(Pmg.message('needtoprovideaname'), null, null, true);
}
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
	pane.serverAction({action: 'Process', query: {id: true, params: {process: 'deleteCalendar', noget: true}}}, {includeWidgets: ['googlecalid']}).then(lang.hitch(this, function(){
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
}
?>
