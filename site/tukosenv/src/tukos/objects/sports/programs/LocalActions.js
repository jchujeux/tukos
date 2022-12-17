define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		viewModeOption: function(optionName, isOptionChange){
			var form = this.form, sessionsWidget = form.getWidget('sptsessions'), column, customizationPath = sessionsWidget.customizationPath;
			this.sessionsFilter = this.sessionsWidget || (new sessionsWidget.store.Filter());
			sessionsWidget.optionHiddenCols = sessionsWidget.optionHiddenCols || [];
			form.optionHiddenWidgets = form.optionHiddenWidgets || [];
			sessionsWidget.customizationPath = '';
			sessionsWidget.isBulk = true;
			sessionsWidget.optionHiddenCols.forEach(function(col){
			    sessionsWidget.toggleColumnHiddenState(col, false);
			});
			sessionsWidget.optionHiddenCols = [];
			form.optionHiddenWidgets.forEach(function(widgetName){
				form.getWidget(widgetName).set('hidden', false);
			});
			form.getWidget('templatesPane').set('hidden', false);
			form.optionHiddenWidgets = [];
			switch (optionName){
			    case 'viewplanned':
			        this.performedColumns.forEach(function(col){
			            if ((column = sessionsWidget.columns[col]) && !column.hidden){
			                sessionsWidget.toggleColumnHiddenState(col, true);
			                sessionsWidget.optionHiddenCols.push(col);
			            }
			        });
					['performedloadchart', 'weekperformedloadchart'].forEach(lang.hitch(this, this.widgetOptionalHide));
					//sessionsWidget.set('collection', sessionsWidget.get('collection').filter(this.sessionsFilter.ne('mode', 'performed')));
					sessionsWidget.extraUserFilters = {mode: ['NOT RLIKE', 'performed']};
					sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
			        break;
			    case 'viewperformed':
			        this.plannedColumns.forEach(function(col){
			            if ((column = sessionsWidget.columns[col]) && !column.hidden){
			                sessionsWidget.toggleColumnHiddenState(col, true);
			                sessionsWidget.optionHiddenCols.push(col);
			            }
			        });
					form.getWidget('templatesPane').set('hidden', true);
					['loadchart', 'weekloadchart'/*, 'templates', 'warmup', 'mainactivity', 'warmdown'*/].forEach(lang.hitch(this, this.widgetOptionalHide));
					//sessionsWidget.set('collection', sessionsWidget.get('collection').filter(this.sessionsFilter.eq('mode', 'performed')));
					sessionsWidget.extraUserFilters = {mode: ['RLIKE', 'performed']};
					sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
			        break;
			    case 'viewall':
					if (isOptionChange){
						sessionsWidget.extraUserFilters = false;
						sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
					}
			        break;
			}
			sessionsWidget.customizationPath = customizationPath;
			sessionsWidget.isBulk = false;
			//sessionsWidget.refresh();
			if (isOptionChange){
            	form.unfreezeWidth = true;
				form.unfrozenWidths = 0;
				form.resize();
				form.unfreezeWidth = false;
				if (form.unfrozenWidths){
					form.needsToFreezeWidth = true;
					form.resize();
					form.needsToFreezeWidth = false;
				}
				form.viewModeOption = optionName;
				if (!Pmg.isRestrictedUser()){
					lang.setObject('customization.viewModeOption', optionName, form);
				}
			}
		},
		widgetOptionalHide: function(widgetName){
			var form = this.form, widget = form.getWidget(widgetName);
			if (!widget.get('hidden')){
				form.getWidget(widgetName).set('hidden', true);
				form.optionHiddenWidgets.push(widgetName);
			}
		},
		programsConfigApplyAction: function(pane){
			const form = this.form, changedValues = pane.changedValues();
			if (!utils.empty(changedValues)){				
				const sessionsWidget = form.getWidget('sptsessions');
				if (changedValues.equivalentDistance){
					form.programsConfig.equivalentDistance = JSON.stringify(pane.getWidget('equivalentDistance').get('collection').fetchSync());
					sessionsWidget.loadChartUtils.updateCharts(sessionsWidget, true);
					lang.setObject('customization.programsConfig.equivalentDistance', form.programsConfig.equivalentDistance, form);
				}
				if (changedValues.spiders){
					form.programsConfig.spiders = JSON.stringify(pane.getWidget('spiders').get('collection').fetchSync());
					Pmg.setFeedback(Pmg.message('savecustomtoupdatespiders'), null, null, true);
					lang.setObject('customization.programsConfig.spiders', form.programsConfig.spiders, form);
				}
			}
		},
		authorizeStrava: function(pane){
			var form = pane.form, programId = form.valueOf('id'), athleteId = form.valueOf('parentid'), coachId = form.valueOf('coach'), contentMessage = athleteId ? '' : Pmg.message('needtodefineathlete', 'sptprograms');
			contentMessage = coachId ? '' : ((contentMessage ? ' & ' : '') + Pmg.message('needtodefinecoach', 'sptprograms'));
			pane.close();
			if (contentMessage){
				Pmg.alert({title: Pmg.message('cannotsynchronizestrava', 'sptprograms'), content: contentMessage});
			}else{
            	Pmg.setFeedback(Pmg.message('actionDoing'));
            	this.form.serverDialog({action:'Process', query: {id: programId, athleteid: athleteId, athleteemail: form.valueOf('sportsmanemail'), coachid: form.valueOf('coach'), coachemail: form.valueOf('coachemail'),
					params:  JSON.stringify({process: 'stravaEmailAuthorize', save: true})}}, form.changedValues(), form.get('postElts'), Pmg.message('actionDone')); 
			}
		},
		synchronizeWithStrava: function(pane){
			var form = pane.form, programId = form.valueOf('id'), athlete = form.valueOf('parentid'),
				contentMessage = !athlete ?  ' & <br> '  + Pmg.message('needtodefineathlete') : '';
			pane.close();
			if (contentMessage){
				Pmg.alert({title: Pmg.message('cannotsynchronizestrava'), content: contentMessage});
			}else{
            	Pmg.setFeedback(Pmg.message('actionDoing'));
            	this.form.serverDialog({action:'Process', query: {id: programId, parentid: athlete, synchrostart: pane.valueOf('stsynchrostart'), synchroend: pane.valueOf('stsynchroend'), synchrostreams: pane.valueOf('synchrostreams'), ignoresessionflag: pane.valueOf('ignoresessionflag'), 
					googlecalid: form.valueOf('googlecalid'), params:  JSON.stringify({process: 'stravaProgramSynchronize', save: true})}}, form.changedValues(), form.get('postElts'), Pmg.message('actionDone')); 
			}
		}
	});
});
