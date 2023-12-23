"use strict";
define(["dojo/_base/declare", "dojo/_base/lang"], 
function(declare, lang){
    const runningWidgets = ['duration', 'distance', 'elevationgain', 'perceivedload', 'perceivedintensity', 'intensitydetails'],
          activityWidgets = ['globalsensation', 'globalsensationdetails', 'environment', 'environmentdetails', 'recovery', 'recoverydetails', 'activitydetails', 'perceivedstress', 'stressdetails', 'mentaldifficulty', 
          	'mentaldifficultydetails'];
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		recordTypeChangeAction: function(newValue, pane){
			switch (Number(newValue)){
				case 1: /* running*/
				case 2: /*bicycle*/
					pane.getWidget('activityPane').set('hidden', false);
					pane.getWidget('activitydetails').set('hidden', true);
					pane.getWidget('runningPane').set('hidden', false);
					pane.getWidget('intensityPane').set('hidden', false);
					pane.getWidget('noteIndicatorsPane').set('hidden', false);
					break;
				case 3: /* other activity*/
					pane.getWidget('activityPane').set('hidden', false);
					pane.getWidget('activitydetails').set('hidden', false);
					pane.getWidget('runningPane').set('hidden', true);
					pane.getWidget('noteIndicatorsPane').set('hidden', false);
					pane.getWidget('intensityPane').set('hidden', true);
					break;
				case 4: /* note / comment*/
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
		accordionRecordTypeChangeAction: function(newValue, pane){
			let i, indicatorWidget;
			switch(Number(newValue)){
				case 1: /*running*/
				case 2: /*bicycle*/
					runningWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', false);
					});
					activityWidgets.forEach(function(widgetName){
						pane.getWidget(widgetName).set('hidden', false);
					});
					pane.getWidget('activitydetails').set('hidden', true);
					pane.getWidget('notecomments').set('hidden', false);
					i = 1;
					while ((indicatorWidget = pane.getWidget('trackindicator' + i))){
						indicatorWidget.set('hidden', false);
						i += 1;
					}
					break;
				case 3: /* other activity*/
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
				case 4: /* Notes / comments*/
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
		accordionExpandAction: function(newValue, rowTitlePane){
			const pane = rowTitlePane.editorPane;
			pane.watchContext = 'server';
			this.accordionRecordTypeChangeAction(newValue, pane);
			pane.markAllChanges = false;
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
			pane.markAllChanges = true;
		},
		desktopAccordionExpandAction: function(newValue, rowTitlePane){
			const pane = rowTitlePane.editorPane;
			pane.watchContext = 'server';
			this.recordTypeChangeAction(newValue, pane);
			pane.markAllChanges = false;
			pane.setWidgets(pane.data);
			pane.watchContext = 'user';
			pane.markAllChanges = true;
			setTimeout(function(){
				if (rowTitlePane.domNode.style.height){//when there is only one row width and height get set to unwanted value
					rowTitlePane.domNode.style.height = '';
					rowTitlePane.domNode.style.width = '';
				}
			}, 100);
		},
		showHideGamePlan: function(){
			const form = this.form, gamePlanPane = form.getWidget('gamePlanPane'), newHiddenValue = !gamePlanPane.get('hidden');
			if (!newHiddenValue){
				form.getWidget('records').deleteDesktopRowTitlePanesChildren();
			}
			gamePlanPane.set('hidden', newHiddenValue);
			form.getWidget('id').set('hidden', newHiddenValue);
			form.getWidget('parentid').set('hidden', newHiddenValue);
			form.resize();
		},
		showHideAnalysis: function(){
			const form = this.form, analysisPane = form.getWidget('roadTrackAnalysis');
			analysisPane.set('hidden', !analysisPane.get('hidden'));
			form.resize();
		}
	});
});
