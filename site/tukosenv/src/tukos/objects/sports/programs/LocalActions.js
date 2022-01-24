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
					['loadchart', 'weekloadchart', 'templates', 'warmup', 'mainactivity', 'warmdown'].forEach(lang.hitch(this, this.widgetOptionalHide));
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
				lang.setObject('customization.viewModeOption', optionName, form);
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
			var form = this.form, sessionsWidget = form.getWidget('sptsessions');
			form.programsConfig = {equivalentDistance: JSON.stringify(pane.valueOf('equivalentdistance'))};
			lang.setObject('customization.programsConfig', form.programsConfig, form);
			sessionsWidget.loadChartUtils.updateCharts(sessionsWidget, true);
		}
	});
});
