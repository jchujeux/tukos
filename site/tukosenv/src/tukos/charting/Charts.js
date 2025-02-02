"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
	const classes = {trend: "tukos/charting/TrendChart", spider: "tukos/charting/SpiderChart", pie: "tukos/charting/PieChart", repartition: "tukos/charting/RepartitionChart", xy: "tukos/charting/XyChart"};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
            const self = this, form = self.form, grid = self.grid, dateCol = self.dateCol, timeCol = self.timeCol, charts = self.charts;
            this.chartTypeOf = {};
            this.subValuesCache = {};
            form.chartWidgets = {};
			form.isCharting = false;
            utils.forEach(charts, function(chart, id){
				if (chart.chartType){
					chart.widgetName = 'chart' + id;
					require([classes[chart.chartType]], function(chartClass){
						form.chartWidgets[chart.widgetName] = new chartClass({form: form, grid: grid, dateCol: dateCol, timeCol: timeCol, valueOf: self.valueOf});
					})
				}
			});
        },
		_setChartsValue: function(){
            const form = this.form;
			let lastChartWidgetToProcess = '', lastChartWidgetInProcess = '';
            if (form.chartWidgets){
				Pmg.addFeedback(Pmg.message('processingcharts'));
	            for (let  chartWidgetName in form.chartWidgets){
					if (!form.getWidget(chartWidgetName).get('hidden')){
						utils.waitUntil(
							function(){return !form.isCharting;}, 
							function(){
								form.isCharting = true;
								lastChartWidgetInProcess = chartWidgetName;
								form.chartWidgets[chartWidgetName].setChartValue(chartWidgetName);},
							100
						);
						lastChartWidgetToProcess = chartWidgetName;
					}
				}
				utils.waitUntil(
					function(){return lastChartWidgetToProcess === lastChartWidgetInProcess && form.isCharting === false},
					function(){Pmg.addFeedback(Pmg.message('allchartsprocessed'))}
				);
			}
		},
		setChartsValue: function(){
			if (!this.debouncedChartsValue){
				this.debouncedChartsValue = utils.debounce(lang.hitch(this, this._setChartsValue), 200);
			}
			this.debouncedChartsValue();
		},
		setChartValue: function(chartWidgetName){
			const form = this.form, chartWidgets = form.chartWidgets;
			chartWidgets && chartWidgets[chartWidgetName] && chartWidgets[chartWidgetName].setChartValue(chartWidgetName);
		},
		valueOf: function(widgetName){
			const nameAndSubName = widgetName.split('|'), form = this.form;
			switch(nameAndSubName.length){
				case 1:
					return form.valueOf(widgetName);
				case 2:
					const name = nameAndSubName[0], subName = nameAndSubName[1], subValuesCache = this.subValuesCache;
					if (subValuesCache[name] && subValuesCache[name][subName] !== undefined){
						return subValuesCache[name][subName];
					}else{
						lang.setObject(name, subName, subValuesCache);
						return undefined;
					}
				default:
					Pmg.setFeedbackAlert(Pmg.message('wrongwidgetsubname'));
			}
		}
    });
}); 

