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
					lang.setObject('customization.viewModeOption', optionName, form);
	                Pmg.tabs.localRefresh({values: true, customization: true});
				}
                return;
			}else{
				this.sessionsFilter = this.sessionsWidget || (new sessionsWidget.store.Filter());
				sessionsWidget.customizationPath = '';
				switch (optionName){
				    case 'viewplanned':
						sessionsWidget.extraUserFilters = {mode: ['NOT RLIKE', 'performed']};
						sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
				        break;
				    case 'viewperformed':
						sessionsWidget.extraUserFilters = {mode: ['RLIKE', 'performed']};
						sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
				        break;
				    case 'viewall':
						sessionsWidget.extraUserFilters = false;
						sessionsWidget.set('collection', sessionsWidget.store.getRootCollection());
				        break;
				}
				sessionsWidget.customizationPath = customizationPath;
			}
		}
	});
});
