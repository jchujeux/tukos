"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "dijit/registry", "tukos/utils", "tukos/dateutils", "tukos/widgetUtils", "tukos/PageManager"], 
function(declare, lang, registry, utils, dutils, wutils,  Pmg){
    const runningWidgets = ['duration', 'distance', 'elevationgain', 'perceivedload', 'perceivedintensity', 'intensitydetails'],
          activityWidgets = ['globalsensation', 'globalsensationdetails', 'environment', 'environmentdetails', 'recovery', 'recoverydetails', 'activitydetails', 'perceivedstress', 'stressdetails', 'mentaldifficulty', 
          	'mentaldifficultydetails'],
          commonWidgets = ['rowId', 'recordtype', 'recorddate', 'notecomments'];
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		afterServerAction: function(){
			const self = this;
			dojo.ready(function(){
				setTimeout(function(){
					self.hideEditionWidgets();
				}, 0);
			});
		},
		hideEditionWidgets: function(){
			const form = this.form;
			form.getWidget('activityPane').set('hidden', true);
			form.getWidget('runningPane').set('hidden', true);
			form.getWidget('intensityPane').set('hidden', true);
			form.getWidget('noteIndicatorsPane').set('hidden', true);
			form.getWidget('recordFiltersInfoPane').set('hidden', true);
			form.getWidget('existingrecord').set('hidden', Pmg.isMobile());
			form.getWidget('newrecord').set('hidden', Pmg.isMobile());
			form.getWidget('existingrecord').set('disabled', false);
			form.getWidget('newrecord').set('disabled', false);
			form.getWidget('actualize').set('hidden', true);
			form.getWidget('visualize').set('hidden', true);
			form.getWidget('cancelmode').set('hidden', true);
			[...runningWidgets, ...activityWidgets, ...commonWidgets].forEach(function(widgetName){
				wutils.setStyleToUnchanged(form.getWidget(widgetName));
			});
			let i = 1, indicatorWidget;
			while (indicatorWidget = form.getWidget('trackindicator' + i)){
				wutils.setStyleToUnchanged(indicatorWidget);
				i += 1;
			}
			form.resize();
		},
		newRecordOnClickAction: function(){
			const form = this.form;
			form.markIfChanged = form.watchOnChange = false;
			form.getWidget('rowId').set('hidden', true);
			form.getWidget('recordFiltersInfoPane').set('hidden', false);
			form.emptyWidgets([...runningWidgets, ...activityWidgets, ...commonWidgets]);
			let i = 1, indicatorWidget;
			while (indicatorWidget = form.getWidget('trackindicator' + i)){
				indicatorWidget.set('value', 0);
				i += 1;
			}
			form.getWidget('actualize').set('label', Pmg.message('addrecord', form.object));
			form.setValueOf('recordtype', '');
			form.setValueOf('recorddate', dutils.formatDate(new Date()));
			form.getWidget('actualize').set('hidden', true);
			form.getWidget('cancelmode').set('hidden', false);
			form.getWidget('existingrecord').set('hidden', true);
			form.getWidget('newrecord').set('disabled', true);
			form.markIfChanged = form.watchOnChange = true;
			form.resize();
			form.resize();
		},
		existingRecordOnClickAction: function(){
			const form = this.form;
			form.markIfChanged = form.watchOnChange = false;
			form.getWidget('rowId').set('hidden', false);
			form.getWidget('recordFiltersInfoPane').set('hidden', false);
			const lastRecord = form.getWidget('records').get('collection').sort('rowId', 'descending').fetchSync()[0];
			form.setValueOf('recorddate', lastRecord.recorddate);
			form.setValueOf('recordtype', lastRecord.recordtype);
			form.setValueOf('rowId', lastRecord.rowId);
			form.getWidget('rowId').set('disabled', false);
			form.getWidget('actualize').set('hidden', true);
			form.getWidget('visualize').set('hidden', false);
			form.getWidget('cancelmode').set('hidden', false);
			form.getWidget('newrecord').set('hidden', true);
			form.getWidget('existingrecord').set('disabled', true);
			form.markIfChanged = form.watchOnChange = true;
			form.resize();
			form.resize();
		},
		rowVisualizeClickAction: function(record){
			const form = this.form;
			form.markIfChanged = form.watchOnChange = false;
			form.getWidget('rowId').set('hidden', false);
			form.getWidget('recordFiltersInfoPane').set('hidden', false);
			form.setValueOf('recorddate', record.recorddate);
			form.setValueOf('recordtype', record.recordtype);
			form.setValueOf('rowId', record.rowId);
			form.getWidget('rowId').set('disabled', true);
			form.getWidget('actualize').set('hidden', true);
			form.getWidget('visualize').set('hidden', false);
			form.getWidget('cancelmode').set('hidden', false);
			form.getWidget('newrecord').set('hidden', true);
			form.getWidget('existingrecord').set('disabled', true);
			form.emptyWidgets([...runningWidgets, ...activityWidgets, ...commonWidgets]);
			this.rowToWidgets(record);
			form.getWidget('actualize').set('label', Pmg.message('actualizerecord', form.object));
			form.getWidget('visualize').set('hidden', true);
			form.getWidget('rowId').set('disabled', true);
			this.recordTypeChangeAction(form.valueOf('recordtype'));
			form.markIfChanged = form.watchOnChange = true;
		},
		recordDateChangeLocalAction(newValue){
			const form = this.form;
			if (form.getWidget('visualize').get('hidden')){
				form.getWidget('actualize').set('hidden', false);
				form.resize();
			}
		},
		recordTypeChangeAction: function(newValue, pane){
			const form = this.form;
			pane = pane || form;
			if (pane !== form || form.getWidget('visualize').get('hidden')){
				switch (Number(newValue)){
					case 1: /* running*/
						pane.getWidget('activityPane').set('hidden', false);
						pane.getWidget('activitydetails').set('hidden', true);
						pane.getWidget('runningPane').set('hidden', false);
						pane.getWidget('intensityPane').set('hidden', false);
						pane.getWidget('noteIndicatorsPane').set('hidden', false);
						break;
					case 2: /* other activity*/
						pane.getWidget('activityPane').set('hidden', false);
						pane.getWidget('activitydetails').set('hidden', false);
						pane.getWidget('runningPane').set('hidden', true);
						pane.getWidget('noteIndicatorsPane').set('hidden', false);
						pane.getWidget('intensityPane').set('hidden', true);
						break;
					case 3: /* note / comment*/
					default:
						pane.getWidget('noteIndicatorsPane').set('hidden', false);
						pane.getWidget('activityPane').set('hidden', true);
						pane.getWidget('runningPane').set('hidden', true);
						pane.getWidget('intensityPane').set('hidden', true);
						break;
				}
				if (pane === form && form.markIfChanged){
					form.getWidget('actualize').set('hidden', false);
					form.getWidget('cancelmode').set('hidden', false);
				}
				if (pane !== form){
					this.accordionRecordTypeChangeAction(newValue, pane);
				}
				//if (pane === form){
					pane.resize();
					pane.resize();
				//}
			}
		},
		accordionRecordTypeChangeAction(newValue, pane){
			let i, indicatorWidget;
			switch(Number(newValue)){
				case 1: /*running*/
					runningWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', false);
					});
					activityWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', false);
					});
					pane.getWidget('activitydetails').set('hidden', true);
					pane.getWidget('notecomments').set('hidden', false);
					i = 1;
					while (indicatorWidget = pane.getWidget('trackindicator' + i)){
						indicatorWidget.set('hidden', false);
						i += 1;
					}
					break;
				case 2: /* other activity*/
					runningWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', true);
					});
					activityWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', false);
					});
					pane.getWidget('activitydetails').set('hidden', false);
					pane.getWidget('notecomments').set('hidden', false);
					i = 1;
					while (indicatorWidget = pane.getWidget('trackindicator' + i)){
						indicatorWidget.set('hidden', false);
						i += 1;
					}
					break;
				case 3: /* Notes / comments*/
					runningWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', true);
					});
					activityWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', true);
					});
					pane.getWidget('activitydetails').set('hidden', true);
					pane.getWidget('notecomments').set('hidden', false);
					i = 1;
					while (indicatorWidget = pane.getWidget('trackindicator' + i)){
						indicatorWidget.set('hidden', false);
						i += 1;
					}
					break;
				default:
					runningWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', true);
					});
					activityWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', true);
					});
					pane.getWidget('activitydetails').set('hidden', true);
					pane.getWidget('notecomments').set('hidden', true);
					i = 1;
					while (indicatorWidget = pane.getWidget('trackindicator' + i)){
						indicatorWidget.set('hidden', true);
						i += 1;
					}
			}
			setTimeout(function(){
				pane.resize();
			}, 100);
		},
		accordionExpandAction(newValue, pane){
			pane.watchContext = 'server';
			this.accordionRecordTypeChangeAction(newValue, pane);
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
		},
		desktopAccordionExpandAction(newValue, pane){
			pane.watchContext = 'server';
			this.recordTypeChangeAction(newValue, pane);
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
		},
		actualize: function(){
			const form = this.form, recordType = Number(form.valueOf('recordtype')), row = {}, recordsWidget = form.getWidget('records');
			switch(recordType){
				case 1:
					runningWidgets.forEach(function(widgetName){
						const widget = form.getWidget(widgetName);
						row[widgetName] = widget.get('value');
						wutils.markAsUnchanged(widget);
					});
				case 2:
					activityWidgets.forEach(function(widgetName){
						const widget = form.getWidget(widgetName);
						row[widgetName] = widget.get('value');
						wutils.markAsUnchanged(widget);
					});
				case 3:
				default:
					commonWidgets.forEach(function(widgetName){
						const widget = form.getWidget(widgetName);
						row[widgetName] = widget.get('value');
						wutils.markAsUnchanged(widget);
					});
					break;
			}
			let i = 1, indicatorWidget;
			const indicatorsCache = {};
			while (indicatorWidget = form.getWidget('trackindicator' + i)){
				row['trackindicator' + i] = indicatorsCache['trackindicator' + i] = indicatorWidget.get('value');
				wutils.markAsUnchanged(indicatorWidget);
				i += 1;
			}
			row['indicatorscache'] = JSON.stringify(indicatorsCache);
			if (!form.getWidget('newrecord').get('hidden')){
				const addedRow = recordsWidget.addRow('last', row);
				form.getWidget('newrecord').set('hidden', true);
				form.getWidget('existingrecord').set('hidden', false);
				form.getWidget('existingrecord').set('disabled', true);
				form.getWidget('actualize').set('label',  Pmg.message('actualizerecord', form.object));
				form.setValueOf('rowId', addedRow.rowId);
				form.getWidget('rowId').set('hidden', false);
			}else{
				recordsWidget.updateRow(row);
			}
			form.getWidget('actualize').set('hidden', true);
			//form.getWidget('cancelmode').set('hidden', true);
			form.resize();
		},
		visualize: function(){
			const form = this.form;
			form.markIfChanged = form.watchOnChange = false;
			const date = form.valueOf('recorddate'), recordType = form.valueOf('recordtype');
			if (date && recordType){
				let records = form.getWidget('records').get('collection').filter({recorddate: date, recordtype: recordType}).fetchSync();
				switch(records.length){
					case 0:
						Pmg.setFeedback(Pmg.message('NorecordFoundAtThatDateAndRecordType', this.object), null, null, beep);
						break;
					case 1:
					default:
						form.emptyWidgets([...runningWidgets, ...activityWidgets, ...commonWidgets]);
						this.rowToWidgets(records[0]);
						form.getWidget('actualize').set('label', Pmg.message('actualizerecord', form.object));
						form.getWidget('actualize').set('hidden', true);
						form.getWidget('visualize').set('hidden', true);
						form.getWidget('rowId').set('disabled', true);
						this.recordTypeChangeAction(form.valueOf('recordtype'));
				}
			}else{
				Pmg.setFeedback(Pmg.message('Needtoprovideadateandrecordtype', this.object), null, null, beep);
			}
			form.markIfChanged = form.watchOnChange = true;
		},
		rowIdChangeAction: function(newValue){
			const form = this.form;
			form.markIfChanged = form.watchOnChange = false;
			const record = form.getWidget('records').get('collection').filter({rowId: Number(newValue)}).fetchSync()[0];
			form.setValueOf('recorddate', record.recorddate);
			form.setValueOf('recordtype', record.recordtype);
			form.markIfChanged = form.watchOnChange = true;
		},
		cancelMode: function(){
			this.hideEditionWidgets();
		},
		rowToWidgets: function(row){
			const form = this.form, recordType = Number(row['recordtype']);
			switch(recordType){
				case 1:
					runningWidgets.forEach(function(widgetName){
						form.setValueOf(widgetName, row[widgetName]);
					});
				case 2:
					activityWidgets.forEach(function(widgetName){
						form.setValueOf(widgetName, row[widgetName]);
					});
				case 3:
				default:
					commonWidgets.forEach(function(widgetName){
						form.setValueOf(widgetName, row[widgetName]);
					});
					break;
			}
			const indicators = row['indicatorscache'] ? JSON.parse(row['indicatorscache']) : {};
			let i = 1, indicatorWidget;
			while (indicatorWidget = form.getWidget('trackindicator' + i)){
				indicatorWidget.set('value', indicators['trackindicator' + i] || 0);
				i += 1;
			}
		},
		editConfigApplyAction: function(pane){
			const form = this.form, changedValues = pane.changedValues();
			if (!utils.empty(changedValues)){				
				form.editConfig = form.editConfig || {};
				if (changedValues.trendchartsperrow){
					form.editConfig.trendchartsperrow = pane.valueOf('trendchartsperrow');
					lang.setObject('customization.editConfig.trendchartsperrow', form.editConfig.trendchartsperrow, form);
				}
				if (changedValues.trendcharts){
					form.editConfig.trendcharts = JSON.stringify(pane.getWidget('trendcharts').get('collection').fetchSync());
					lang.setObject('customization.editConfig.trendcharts', form.editConfig.trendcharts, form);
				}
				Pmg.setFeedback(Pmg.message('savecustomtoupdatetrendcharts'), null, null, true);
			}
		},
		showHideGamePlan: function(){
			const form = this.form, gamePlanPane = form.getWidget('gamePlanPane'), newHiddenValue = !gamePlanPane.get('hidden');
			gamePlanPane.set('hidden', newHiddenValue);
			form.getWidget('id').set('hidden', newHiddenValue);
			form.getWidget('parentid').set('hidden', newHiddenValue);
			form.resize();
		},
		showHideAnalysis: function(){
			const form = this.form, analysisPane = form.getWidget('roadTrackAnalysis');
			analysisPane.set('hidden', !analysisPane.get('hidden'));
			form.resize();
		},
	});
});
