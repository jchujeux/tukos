"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils"], 
function(declare, lang, utils){
	const classes = {trend: "tukos/charting/TrendChart", spider: "tukos/charting/SpiderChart"};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
            const self = this, requiredClasses = {}, form = self.form, grid = self.grid, dateCol = self.dateCol, charts = self.charts;
            this.chartTypeOf = {}
            for (const chart of charts){
                if (chart.chartType){
                	chart.widgetName = 'chart' + chart.id;
                	requiredClasses[chart.chartType] = classes[chart.chartType];
                	this.chartTypeOf[chart.widgetName] = chart.chartType;
				}
            }
            if (!utils.empty(requiredClasses)){
            	const requiredTypes = Object.keys(requiredClasses);
	            form.chartUtils = {};
	            require(requiredTypes.map(function(i){return requiredClasses[i];}), function(){
	                for (let i in requiredTypes){
	                    form.chartUtils[requiredTypes[i]] = new arguments[i]({form: form, grid: grid, dateCol: dateCol});
	                }
				});
			}
        },
		_setChartsValue: function(){
            const form = this.form, charts = this.charts;
            if (form.chartUtils){
	            for (const chart of charts){
	                form.chartUtils[chart.chartType] && form.chartUtils[chart.chartType].setChartValue(chart.widgetName);
	            }
			}
		},
		setChartsValue: function(){
			if (!this.debouncedChartsValue){
				this.debouncedChartsValue = utils.debounce(lang.hitch(this, this._setChartsValue), 200);
			}
			this.debouncedChartsValue();
		},
		setChartValue: function(chartWidgetName){
			const form = this.form, chartUtilsInstance = form.chartUtils && form.chartUtils[this.chartTypeOf[chartWidgetName]];
			chartUtilsInstance && chartUtilsInstance.setChartValue(chartWidgetName);
		}
    });
}); 

