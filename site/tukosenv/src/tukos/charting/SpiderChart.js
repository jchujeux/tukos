"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/hiutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/charting/chartsUtils", "tukos/PageManager"], 
function(declare, lang, utils, dutils, hiutils, expressionFilter, expressionEngine, chartsUtils, Pmg){
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
					const grid = self.grid, dateCol = self.dateCol, timeCol = self.timeCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter), chartFilter = chartWidget.get('chartFilter'), precision = {};
					let collection;
					if (chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartFilter)).sort([{property: dateCol}, {property: timeCol}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: timeCol}]);
					}
					self.recursionDepth +=1;
					let kpisDescription = utils.toObject(utils.toNumeric(chartWidget.kpisToInclude, 'id'), 'rowId'), kpiData = {}, expKpi = {}, chartData = [], axes = {}, series = {}, kpiFilters = {}, 
						itemsSets = (chartWidget.itemsSetsToInclude ? utils.toObject(utils.toNumeric(chartWidget.itemsSetsToInclude, 'id'), 'rowId') : {1: {setName: Pmg.message('allitemstodate')}}), 
						tableColumns = {kpi: {label: Pmg.message('kpi', form.object), field: 'kpi', renderCell: 'renderContent'}}, idProperty = collection.idProperty, collectionData = utils.toNumericValues(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf);
					utils.forEach(kpisDescription, function(kpiDescription){
						try{
							const kpiName = kpiDescription.name + (kpiDescription.tooltipunit || '');
							chartData.push({kpi: kpiName});
							axes[kpiName] = {'type': 'Base', min: kpiDescription.axisMin || 0, max: kpiDescription.axisMax};
							precision[kpiName] = kpiDescription.axisPrecision || 0;
							if (kpiDescription.kpiFilter){
								kpiFilters[kpiName] = expFilter.expressionToValue(kpiDescription.kpiFilter);
							}
						}catch(e){
							Pmg.addFeedback(Pmg.message('errorkpifilter') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
						}
					});
					const params = chartWidget.plotsToInclude ? chartWidget.plotsToInclude[1] : {}, 
						  plots =  {theSpider: {'type': 'Spider', labelOffset: params.labelOffset || -10, radius: params.spiderRadius, maxLabelWidthShift: params.maxLabelWidthShift, divisions:  params.divisions || 5, precision: precision, seriesFillAlpha: 0.1,
												seriesWidth: 2, markerSize: params.markerSiwe || 5,	axisFont: params.axisFont || "normal normal normal 11pt Arial"}};
					let previousKpiValuesCache = {};
					utils.forEach(itemsSets, function(set){
						try{
							let setName = set.setName, filterString = chartsUtils.setFilterString(set, expression, dateCol), kpiDate = expression.expressionToValue(set.kpidate), setCollection = collection, setData = collectionData, previousToDate, previousData = [];
							if (filterString){
								setCollection = collection.filter(expFilter.expressionToValue(filterString));
								setData = utils.toNumericValues(setCollection.fetchSync(), grid);
							}
							previousKpiValuesCache[setName] = {};
							let setExp = expressionEngine.expression(utils.toNumericValues(setData, grid), idProperty, missingItemsKpis, valueOf, previousKpiValuesCache[setName], [], kpiDate);
							series[setName] = {value: {key: 'kpi', value: setName, tooltip: setName + 'Tooltip'}, 
								options: {plot: 'theSpider', fill: set.fillColor || 'black', hasFill: set.fill, stroke: {color: set.fillColor || 'black', style: set.kpimode === 'planned' ? 'shortDash' : ''}}};
							tableColumns[setName] = {label: hiutils.htmlToText(setName), field: setName/*, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}*/};
							let setKpiData = (kpiData[setName] = {}), setExpKpi = (expKpi[setName] = {});
							utils.forEach(kpisDescription, function(kpiDescription){
								const kpiName = kpiDescription.name;
								if (kpiDescription.kpiFilter){
									setKpiData[kpiName] = utils.toNumericValues(setCollection.filter(kpiFilters[kpiName]).fetchSync(), grid);
									setExpKpi[kpiName] = expressionEngine.expression(setKpiData[kpiName], idProperty, missingItemsKpis, valueOf, previousKpiValuesCache[setName], [], kpiDate);
								}else{
									setKpiData[kpiName] = setData;
									setExpKpi[kpiName] = setExp;
								}
							});
						}catch(e){
							Pmg.addFeedback(Pmg.message('erroritemsset') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('set') + ': ' + JSON.stringify(set));
						}
					});
					utils.forEach(itemsSets, function(set){
						let i = 0, setName = set.setName;
						utils.forEach(kpisDescription, function(kpiDescription){
							try{
								//const value = expKpi[setName][kpiDescription.name].expressionToValue(set.kpimode === 'planned' && kpiDescription.plannedkpicol ? "LAST('$' + kpiDescription.plannedkpicol)" : kpiDescription.kpi);
								const value = set.kpimode === 'planned' && kpiDescription.plannedkpicol && kpiDescription.plannedkpicol[0] !== '$'
									? kpiDescription.plannedkpicol 
									: expKpi[setName][kpiDescription.name].expressionToValue(set.kpimode === 'planned' && kpiDescription.plannedkpicol ? "LAST(kpiDescription.plannedkpicol)" : kpiDescription.kpi);
								chartData[i].id = i;
								if (Array.isArray(value)){//this is untested, and potentially not useful for Spiders
									for (const subValue of value){
										const subName = kpiDescription.name + subValue[0], yValue = subValue[1];
										chartData[i][setName] = value;
										chartData[i][setName + 'Tooltip'] = setName + "<br/>" + subName + ":<br/>" + (kpiDescription.displayformat ? utils.transform(yValue, kpiDescription.displayformat) : yValue) + ' ' + (kpiDescription.tooltipunit || '');
										i += 1;
									}
								}else{
									chartData[i][setName] = value;
									chartData[i][setName + 'Tooltip'] = setName + "<br/>" + kpiDescription.name + ":<br/>" + (kpiDescription.displayformat ? utils.transform(value, kpiDescription.displayformat) : value) + ' ' + (kpiDescription.tooltipunit || '');
									i += 1;
								}
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
							}
						})
					});
					chartsUtils.processMissingKpis(missingItemsKpis, grid, self, chartWidgetName, chartData, chartData, tableColumns, axes, plots, series);
				});
			}		  
		}
    });
}); 

