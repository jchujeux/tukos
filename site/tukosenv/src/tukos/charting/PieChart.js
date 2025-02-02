"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/charting/chartsUtils", "tukos/PageManager"], 
function(declare, lang, utils, dutils, expressionFilter, expressionEngine, chartsUtils, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, valueOf = self.valueOf.bind(self), chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {};
			if (!hidden && chartWidget.kpisToInclude){
				dojo.ready(function(){
					const grid = self.grid, dateCol = self.dateCol, timeCol = self.timeCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter);
					let collection;
					if (chartWidget.chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: timeCol}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: timeCol}]);
					}
					self.recursionDepth +=1;
					let kpisDescription = chartWidget.kpisToInclude, kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, tableColumns = {name: {label: Pmg.message('name', form.object), field: 'name', renderCell: 'renderContent'}, value: {label: Pmg.message('value', form.object), field: 'value', renderCell: 'renderContent'}},
						idProperty = collection.idProperty, collectionData = utils.toNumericValues(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf);
					const plots =  {thePie: {'type': 'Pie', labelOffset: -10}};
					let previousKpiValuesCache = {}, i = 0;
					series[0] = {value: {text: 'name', y: 'value', tooltip: 'tooltip'}, options: {plot: 'thePie'/*, fill: set.fillColor || 'black'*/}};
					utils.forEach(kpisDescription, function(kpiDescription){
						try{
							let filterString = chartsUtils.setFilterString(kpiDescription, expression, dateCol), kpiDate = expression.expressionToValue(kpiDescription.kpidate), kpiCollection, previousToDate, previousData = [], expKpi;
							if (filterString){
								kpiCollection = collection.filter(expFilter.expressionToValue(filterString));
								kpiData = utils.toNumericValues(kpiCollection.fetchSync(), grid);
								previousToDate = dutils.dateString(kpiData[kpiData.length - 1][dateCol], [-1, 'day']);
								previousData = utils.toNumericValues(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
							}else{
								kpiCollection = collection;
								kpiData = collectionData;
							}
							expKpi = expressionEngine.expression(kpiData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
								//chartData[kpiDescription.categoryIndex][kpiDescription.kpiIndex + 'Tooltip'] = kpiDescription.name + ': ' + kpiValue + (kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit);
							const value = expKpi.expressionToValue(kpiDescription.kpi);
							if (Array.isArray(value)){
								for (const subValue of value){
									const subName = kpiDescription.name + subValue[0];
									chartData[i] = {id: i, name: subName, value: subValue[1], tooltip: subName + ': ' + (kpiDescription.displayformat ? utils.transform(subValue[1], kpiDescription.displayformat) : subValue[1]) + ' ' + (kpiDescription.tooltipunit || '')};
									i += 1;
								}
							}else{
								chartData[i] = {id: i, name: kpiDescription.name, value: value, tooltip: kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(value, kpiDescription.displayformat) : value) + ' ' + (kpiDescription.tooltipunit || '')};
								i += 1;
							}
						}catch(e){
							Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
						}
					});
					chartsUtils.processMissingKpis(missingItemsKpis, grid, self, chartWidgetName, chartData, chartData, tableColumns, axes, plots, series);
				});
			}		  
		}
    });
}); 

