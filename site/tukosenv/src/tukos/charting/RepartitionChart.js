"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/charting/chartsUtils", "tukos/PageManager"], 
function(declare,lang, utils, dutils, expressionFilter, expressionEngine, chartsUtils, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, xLabels = [];
			if (!hidden  && chartWidget.kpisToInclude){
				dojo.ready(function(){
					let collection, horizontalAxisDescription;
					const grid = self.grid, dateCol = self.dateCol, timeCol = self.timeCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter),
						 kpisDescription = utils.toObject(utils.toNumeric(chartWidget.kpisToInclude, 'id'), 'rowId'), series = {}, chartData = [], tableData = [], axesDescription = chartWidget.axesToInclude, axes = {},
						 plotsDescription = chartWidget.plotsToInclude, plots = {}, tableColumns = {};
					self.recursionDepth +=1;
					utils.forEach(axesDescription, function(axisDescription){
						axes[axisDescription.name] = axisDescription;
						if (!axisDescription.vertical){
							horizontalAxisDescription = axisDescription;
							axisDescription.labelFunc = function(textValue, rawValue){
								return xLabels[rawValue];
							}
						}
					});
					if (chartWidget.chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: timeCol}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: timeCol}]);
					}
					const idProperty = collection.idProperty, collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						const data = utils.toNumericValues(collectionData, grid), valueOf = self.valueOf.bind(self);
						let previousKpiValuesCache = {}, filter = collection.Filter(), expression = expressionEngine.expression(data, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache);
						utils.forEach(plotsDescription, function(plotDescription){
							plots[plotDescription.name] = plotDescription;
						});
						let kpiIndex = 0, categoryIndex = 0, kpiNames = {}, categories = {};
						tableColumns[0] = {field: 0, label: Pmg.message('Category')};
						utils.forEach(kpisDescription, function(kpiDescription, index){
							const name = kpiDescription.name, category = kpiDescription.category;
							if(typeof kpiNames[name] === "undefined"){
								kpiNames[name] = kpiIndex;
								series[kpiIndex] = {value: {y: kpiIndex, tooltip: kpiIndex + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
								kpiIndex += 1;
								tableColumns[kpiIndex] = {field: kpiIndex, label: kpiDescription.name};
							}
							kpiDescription.kpiIndex = kpiNames[name];
						});
						const fillChartAndDataTables = function(kpiDescription, category, value){
							if (typeof categories[category] === "undefined"){
								categories[category] = categoryIndex;
								chartData[categoryIndex] = {};
								tableData[categoryIndex] = {};
								categoryIndex += 1;
								xLabels[categoryIndex] = Pmg.message(category, grid.object);
							}
							kpiDescription.categoryIndex = categories[category];
							tableData[kpiDescription.categoryIndex][kpiDescription.kpiIndex+1] = value;
							if (isNaN(value) && kpiDescription.absentiszero){
								value = 0;
							}
							chartData[kpiDescription.categoryIndex][kpiDescription.kpiIndex + 'Tooltip'] = kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(value, kpiDescription.displayformat) : value) + ' ' + (kpiDescription.tooltipunit || '');
							if (kpiDescription.scalingfactor){
								value = value * kpiDescription.scalingfactor;
							}
							chartData[kpiDescription.categoryIndex][kpiDescription.kpiIndex] = value;
						}
						utils.forEach(kpisDescription, function(kpiDescription){
							try{
								const category = kpiDescription.category;
								let filterString = chartsUtils.setFilterString(kpiDescription, expression, dateCol), kpiDate = expression.expressionToValue(kpiDescription.kpidate), kpiCollection = collection, kpiData = collectionData, previousToDate, previousData = [], kpiValue;
								if (filterString){
									kpiCollection = collection.filter(expFilter.expressionToValue(filterString));
									kpiData = utils.toNumericValues(kpiCollection.fetchSync(), grid);
									previousToDate = dutils.dateString(kpiData[kpiData.length - 1][dateCol], [-1, 'day']);
									previousData = utils.toNumericValues(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
								}
								expression = expressionEngine.expression(kpiData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
								kpiValue = expression.expressionToValue(kpiDescription.kpi);
								if (typeof kpiValue === 'string'){
									kpiValue = JSON.parse(kpiValue);
								}
								if (Array.isArray(kpiValue)){
									for (const subValue of kpiValue){
										fillChartAndDataTables(kpiDescription, category + ' ' + subValue[0], subValue[1]);
									}
								}/*else{
									fillChartAndDataTables(kpiDescription, category, kpiValue);
								}*/
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
							}
						});
						if (horizontalAxisDescription.max && chartData.length > horizontalAxisDescription.max && horizontalAxisDescription.adjustmax){
							delete horizontalAxisDescription.max;
						}
					}
					chartsUtils.processMissingKpis(missingItemsKpis, grid, self, chartWidgetName, chartData, tableData, tableColumns, axes, plots, series);
				});
			}		  
		}
    });
}); 

