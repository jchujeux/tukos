"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
	const classes = {trend: "tukos/charting/TrendChart", spider: "tukos/charting/SpiderChart", pie: "tukos/charting/PieChart", repartition: "tukos/charting/RepartitionChart", xy: "tukos/charting/XyChart"},
		  subValuesCache = {};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
            const self = this, form = self.form, grid = self.grid, dateCol = self.dateCol, timeCol = self.timeCol, charts = self.charts;
            form.chartWidgets = {};
			form.isCharting = false;
            utils.forEach(charts, function(chart, id){
				if (chart.chartType){
					chart.widgetName = 'chart' + id;
					require([classes[chart.chartType]], function(chartClass){
						form.chartWidgets[chart.widgetName] = new chartClass({form: form, grid: grid, dateCol: dateCol, timeCol: timeCol, valueOf: self.valueOf, updateSubValuesCache: self.updateSubValuesCache});
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
					const id = form.valueOf(nameAndSubName[0]), property = nameAndSubName[1];
					if (!subValuesCache[id]){
						subValuesCache[id] = {};
					}
					if (subValuesCache[id][property] !== undefined){
						return subValuesCache[id][property];
					}else{
						return subValuesCache[id][property] = undefined;
					}
				default:
					Pmg.setFeedbackAlert(Pmg.message('wrongwidgetsubname'));
			}
		},
		updateSubValuesCache: function(){
			const missingSubValues = {};
			utils.forEach(subValuesCache, function(properties, id){
				utils.forEach(properties, function(value, property){
					if (value === undefined){
						if(!missingSubValues[id]){
							missingSubValues[id] = [property];
						}else{
							missingSubValues[id].push(property);
						}
					}
				});
			});
			if (utils.empty(missingSubValues)){
				return false;
			}else{
				return Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'Get', query: {params: {actionModel: 'GetItems'}}}, {data: missingSubValues}).then(
				    function (response){
				        utils.forEach(response.data, function(properties, id){
							utils.forEach(properties, function(propertyValue, propertyName){
								subValuesCache[id][propertyName] = propertyValue;
							});
						});
						return true;
				    }
				);
			}
		}
    });
}); 

