define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		indicatorsConfigApplyAction: function(pane){
			const form = this.form, changedValues = pane.changedValues();
			if (!utils.empty(changedValues)){				
				if (changedValues.indicators){
					form.indicatorsConfig = form.indicatorsConfig || {};
					form.indicatorsConfig.indicators = JSON.stringify(pane.getWidget('indicators').get('collection').fetchSync());
					Pmg.setFeedback(Pmg.message('savecustomforeffect'), null, null, true);
					lang.setObject('customization.indicatorsConfig.indicators', form.indicatorsConfig.indicators, form);
				}
			}
		},
		indicatorsExportAction: function(indicators){
			const form = this.form;
			let result = '';
			for (let indicator of indicators){
				result += '<br><div style="font-size: 4;">' + form.getWidget(indicator).label + '<br></div><div style="text-align: center;>"';
				result += form.displayedHtmlOf(indicator);
				result += '</div>';
			}
			return result;
		}
	});
});
