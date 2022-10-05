"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "dijit/registry", "tukos/utils", "tukos/dateutils", "tukos/widgetUtils", "tukos/PageManager"], 
function(declare, lang, registry, utils, dutils, wutils,  Pmg){
    const runningWidgets = ['duration', 'distance', 'elevationgain', 'perceivedload', 'perceivedintensity', 'intensitydetails'],
          activityWidgets = ['globalsensation', 'globalsensationdetails', 'environment', 'environmentdetails', 'recovery', 'recoverydetails', 'activitydetails', 'perceivedstress', 'stressdetails', 'mentaldifficulty', 
          	'mentaldifficultydetails'];
          //commonWidgets = ['rowId', 'recordtype', 'recorddate', 'notecomments'];
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		recordTypeChangeAction: function(newValue, pane){
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
			this.accordionRecordTypeChangeAction(newValue, pane);
			pane.resize();
			pane.resize();
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
			pane.markAllChanges = false;
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
			pane.markAllChanges = true;
		},
		desktopAccordionExpandAction(newValue, pane){
			pane.watchContext = 'server';
			this.recordTypeChangeAction(newValue, pane);
			pane.markAllChanges = false;
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
			pane.markAllChanges = true;
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
