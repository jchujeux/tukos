"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare, lang, utils, dutils, expressionFilter, expressionEngine, Pmg){
	const setFilterString = function (set, expression, dateCol){
		const filterStrings = [];
		if (set.firstdate){
			filterStrings.push('"' + dateCol + '" >= "' + expression.expressionToValue(set.firstdate) + '"');
		}
		if (set.lastdate){
			filterStrings.push('"' + dateCol + '" <= "' + expression.expressionToValue(set.lastdate) + '"');
		}
		return filterStrings.join(' AND ');
	};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, valueOf = lang.hitch(form, form.valueOf), chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex;
			if (!hidden && chartWidget.kpisToInclude){
				dojo.ready(function(){
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter), collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					let kpisDescription = JSON.parse(chartWidget.kpisToInclude), itemsSets = (chartWidget.itemsSetsToInclude && JSON.parse(chartWidget.itemsSetsToInclude)) || [{setName: Pmg.message('allitemstodate')}], kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, kpiFilters = {}, tableColumns = {kpi: {label: Pmg.message('kpi', 'sptprograms'), field: 'kpi', renderCell: 'renderContent'}}, collectionData = utils.toNumeric(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, missingItemsKpis, valueOf);
					for (const kpiDescription of kpisDescription){
						const kpiName = kpiDescription.name + (kpiDescription.tooltipunit || '');
						chartData.push({kpi: kpiName});
						axes[kpiName] = {'type': 'Base', min: kpiDescription.axisMin || 0, max: kpiDescription.axisMax};
						if (kpiDescription.kpiFilter){
							kpiFilters[kpiName] = expFilter.expressionToValue(kpiDescription.kpiFilter);
						}
					}
					const plots =  {theSpider: {'type': 'Spider', labelOffset: -10, divisions:  4, precision: 0, seriesFillAlpha: 0.2, seriesWidth: 2}};
					let previousKpiValuesCache = {};
					for (const set of itemsSets){
						let setName = set.setName, filterString = setFilterString(set, expression, dateCol), kpiDate = expression.expressionToValue(set.kpidate), setCollection = collection, setData = collectionData, previousToDate, previousData = [];
						if (filterString){
							setCollection = collection.filter(expFilter.expressionToValue(filterString));
							setData = utils.toNumeric(setCollection.fetchSync(), grid);
							previousToDate = dutils.dateString(setData[setData.length - 1][dateCol], [-1, 'day']);
							previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
						}else{
							setCollection = collection;
							
						}
						let setExp = expressionEngine.expression(utils.toNumeric(setData, grid), missingItemsKpis, valueOf, {}, previousData, kpiDate);
						series[setName] = {value: {key: 'kpi', value: setName}, options: {plot: 'theSpider', fill: set.fillColor || 'black'}};
						tableColumns[setName] = {label: setName, field: setName, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}};
						let setKpiData = (kpiData[setName] = {}), setExpKpi = (expKpi[setName] = {});
						for (const kpiDescription of kpisDescription){
							if (kpiDescription.kpiFilter){
								setKpiData[kpiDescription.kpiName] = utils.toNumeric(setCollection.filter(kpiFilters[kpiDescription.kpiName]).fetchSync(), grid);
								setExpKpi[kpiDescription.kpiName] = expressionEngine.expression(setKpiData[kpiDescription.kpiName], missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
							}else{
								setKpiData[kpiDescription.kpiName] = setData;
								setExpKpi[kpiDescription.kpiName] = setExp;
							}
						}
					}
					for (const set of itemsSets){
						let i = 0, setName = set.setName;
						for (const kpiDescription of kpisDescription){
							chartData[i][setName] = expKpi[setName][kpiDescription.kpiName].expressionToValue(kpiDescription.kpi);
							i += 1;
						}
					}
					if (!utils.empty(missingItemsKpis)){
					    let data = {}, indexToIdp = {};
						utils.forEach(missingItemsKpis, function(value, idp){
							let index = grid.store.getSync(idp)[missingKpisIndex];
							if (index){
								data[index] = missingItemsKpis[idp];
								indexToIdp[index] = idp;
							}
						});
					    if (!utils.empty(data)){
						    Pmg.serverDialog({action: 'Process', object: grid.object, view: 'edit', query: {programId: form.valueOf('id'), athlete: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
						            function(response){
						           		const kpis = response.data.kpis;
										utils.forEach(kpis, function(kpi, index){
											let idp = indexToIdp[index], itemKpis = kpis[index];
											utils.forEach(itemKpis, function(kpi, j){
												grid.updateDirty(idp, j, kpi);
											});
										});
										self.setChartValue(chartWidgetName);//recursive call. Risk of infinite loop ?
						            },
						            function(error){
						                console.log('error:' + error);
						            }
						    );
						}else{
							chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
						}
					}else{
						chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
					}
				});
			}		  
		}
    });
}); 

