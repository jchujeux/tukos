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
		if (set.itemsFilter){
			filterStrings.push(set.itemsFilter);
		}
		return filterStrings.join(' AND ');
	};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, valueOf = lang.hitch(form, form.valueOf), chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex;
			if (!hidden && chartWidget.kpisToInclude){
				dojo.ready(function(){
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter);
					let collection;
					if (chartWidget.chartFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					let kpisDescription = JSON.parse(chartWidget.kpisToInclude), itemsSets = (chartWidget.itemsSetsToInclude && JSON.parse(chartWidget.itemsSetsToInclude)) || [{setName: Pmg.message('allitemstodate')}], kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, kpiFilters = {}, tableColumns = {kpi: {label: Pmg.message('kpi', form.object), field: 'kpi', renderCell: 'renderContent'}}, idProperty = collection.idProperty, collectionData = utils.toNumeric(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf);
					for (const kpiDescription of kpisDescription){
						try{
							const kpiName = kpiDescription.name + (kpiDescription.tooltipunit || '');
							chartData.push({kpi: kpiName});
							axes[kpiName] = {'type': 'Base', min: kpiDescription.axisMin || 0, max: kpiDescription.axisMax};
							if (kpiDescription.kpiFilter){
								kpiFilters[kpiName] = expFilter.expressionToValue(kpiDescription.kpiFilter);
							}
						}catch(e){
							Pmg.addFeedback(Pmg.message('errorkpifilter') + ': ' + e.message + ' - ' + Pmg.message('filter') + ': ' + kpiDescription.kpi);
						}
					}
					const plots =  {theSpider: {'type': 'Spider', labelOffset: -10, divisions:  4, precision: 0, seriesFillAlpha: 0.2, seriesWidth: 2, markerSize: 5}};
					let previousKpiValuesCache = {};
					for (const set of itemsSets){
						try{
							let setName = set.setName, filterString = setFilterString(set, expression, dateCol), kpiDate = expression.expressionToValue(set.kpidate), setCollection = collection, setData = collectionData, previousToDate, previousData = [];
							if (filterString){
								setCollection = collection.filter(expFilter.expressionToValue(filterString));
								setData = utils.toNumeric(setCollection.fetchSync(), grid);
								previousToDate = dutils.dateString(setData[setData.length - 1][dateCol], [-1, 'day']);
								previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
							}
							previousKpiValuesCache[setName] = {};
							let setExp = expressionEngine.expression(utils.toNumeric(setData, grid), idProperty, missingItemsKpis, valueOf, previousKpiValuesCache[setName], previousData, kpiDate);
							series[setName] = {value: {key: 'kpi', value: setName, tooltip: setName + 'Tooltip'}, options: {plot: 'theSpider', fill: set.fillColor || 'black'}};
							tableColumns[setName] = {label: setName, field: setName, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}};
							let setKpiData = (kpiData[setName] = {}), setExpKpi = (expKpi[setName] = {});
							for (const kpiDescription of kpisDescription){
								const kpiName = kpiDescription.name;
								if (kpiDescription.kpiFilter){
									setKpiData[kpiName] = utils.toNumeric(setCollection.filter(kpiFilters[kpiName]).fetchSync(), grid);
									setExpKpi[kpiName] = expressionEngine.expression(setKpiData[kpiName], idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
								}else{
									setKpiData[kpiName] = setData;
									setExpKpi[kpiName] = setExp;
								}
							}
						}catch(e){
							Pmg.addFeedback(Pmg.message('erroritemsset') + ': ' + e.message + ' - ' + Pmg.message('set') + ': ' + JSON.stringify(set));
						}
					}
					for (const set of itemsSets){
						let i = 0, setName = set.setName;
						for (const kpiDescription of kpisDescription){
							try{
/*
							const value = expKpi.expressionToValue(kpiDescription.kpi);
							if (Array.isArray(value)){
								for (const subValue of value){
									const subName = kpiDescription.name + subValue[0];
									chartData[i] = {name: subName, value: subValue[1], tooltip: subName + ': ' + (kpiDescription.displayformat ? utils.transform(subValue[1], kpiDescription.displayformat) : subValue[1]) + ' ' + (kpiDescription.tooltipunit || '')};
									i += 1;
								}
							}else{
								chartData[i] = {name: kpiDescription.name, value: value, tooltip: kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(value, kpiDescription.displayformat) : value) + ' ' + (kpiDescription.tooltipunit || '')};
								i += 1;
							}
*/
								const value = expKpi[setName][kpiDescription.name].expressionToValue(kpiDescription.kpi);
								if (Array.isArray(value)){
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
								Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
							}
						}
					}
					if (missingKpisIndex && !utils.empty(missingItemsKpis)){
					    let data = {}, indexToIdp = {};
						utils.forEach(missingItemsKpis, function(value, idp){
							let index = grid.store.getSync(idp)[missingKpisIndex];
							if (index){
								data[index] = missingItemsKpis[idp];
								indexToIdp[index] = idp;
							}
						});
					    if (!utils.empty(data)){
						    if (self.recursionDepth > 2){
								Pmg.addFeedback(Pmg.message('too many recursions') + ': ' + self.recursionDepth + ' (SpiderChart)');
								self.recursionDepth = 0;
								return;
							}
						    Pmg.serverDialog({action: 'Process', object: grid.object, view: 'edit', query: {programId: form.valueOf('id'), athlete: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
						            function(response){
						           		const kpis = response.data.kpis;
										utils.forEach(kpis, function(kpi, index){
											let idp = indexToIdp[index], itemKpis = kpis[index];
											utils.forEach(itemKpis, function(kpi, j){
												grid.updateDirty(idp, j, kpi);
												if (kpi === false){
													Pmg.addFeedback('Pmg.serverKpierror' + ': ' + ' - ' + Pmg.message('col') + ': ' + j + Pmg.message('index') + ': ' + index);
												}
											});
										});
										self.setChartValue(chartWidgetName);//recursive call. Risk of infinite loop ?
						            },
						            function(error){
						                console.log('error:' + error);
						            }
						    );
						}else{
							chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
							self.recursionDepth = 0;
						}
					}else{
						chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
						self.recursionDepth = 0;
					}
				});
			}		  
		}
    });
}); 

