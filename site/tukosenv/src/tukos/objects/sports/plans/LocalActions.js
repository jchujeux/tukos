define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		viewModeOption: function(optionName, isOptionChange, form){
			var self = this, form = form || this.form, sessionsWidget = form.getWidget('sptworkouts'), column, customizationPath = sessionsWidget.customizationPath;
			if ((optionName === form.viewModeOption) && isOptionChange){
				return;
			}
			if (isOptionChange){
				if (form.parent.inLocalRefresh){
					Pmg.addFeedback(Pmg.message('actionnotcompletedwait'));
				}else{
					form.viewModeOption = optionName;
					//if (!Pmg.isRestrictedUser()){
						lang.setObject('customization.viewModeOption', optionName, form);
					//}
	                //Pmg.tabs.refresh('Tab', {}, {values: true, customization: true});
	                Pmg.tabs.localRefresh({values: true, customization: true});
				}
                return;
			}else{
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
						//['performedloadchart', 'weekperformedloadchart'].forEach(lang.hitch(this, this.widgetOptionalHide));
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
						//['loadchart', 'weekloadchart'/*, 'templates', 'warmup', 'mainactivity', 'warmdown'*/].forEach(lang.hitch(this, this.widgetOptionalHide));
						//sessionsWidget.set('collection', sessionsWidget.get('collection').filter(this.sessionsFilter.eq('mode', 'performed')));
						sessionsWidget.extraUserFilters = {mode: ['RLIKE', 'performed']};
						sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
				        break;
				    case 'viewall':
						//if (isOptionChange){
							sessionsWidget.extraUserFilters = false;
							sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
						//}
				        break;
				}
				sessionsWidget.customizationPath = customizationPath;
				sessionsWidget.isBulk = false;
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
				const sessionsWidget = form.getWidget('sptworkouts');
				if (changedValues.equivalentDistance){
					form.programsConfig.equivalentDistance = JSON.stringify(pane.getWidget('equivalentDistance').get('collection').fetchSync());
					lang.setObject('customization.programsConfig.equivalentDistance', form.programsConfig.equivalentDistance, form);
				}
				if (changedValues.spiders){
					form.programsConfig.spiders = JSON.stringify(pane.getWidget('spiders').get('collection').fetchSync());
					Pmg.setFeedback(Pmg.message('savecustomtoupdatespiders'), null, null, true);
					lang.setObject('customization.programsConfig.spiders', form.programsConfig.spiders, form);
				}
			}
		},
	});
});
