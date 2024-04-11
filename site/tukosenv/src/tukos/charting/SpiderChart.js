"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/hiutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare, lang, utils, dutils, hiutils, expressionFilter, expressionEngine, Pmg){
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
			var self = this, form = this.form, valueOf = self.valueOf.bind(self), chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex;
			if (!hidden && chartWidget.kpisToInclude){
				dojo.ready(function(){
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter), chartFilter = chartWidget.get('chartFilter'), precision = {};
					let collection;
					if (chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					self.recursionDepth +=1;
					let kpisDescription = JSON.parse(chartWidget.kpisToInclude), itemsSets = (chartWidget.itemsSetsToInclude && JSON.parse(chartWidget.itemsSetsToInclude)) || [{setName: Pmg.message('allitemstodate')}], kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, kpiFilters = {}, tableColumns = {kpi: {label: Pmg.message('kpi', form.object), field: 'kpi', renderCell: 'renderContent'}}, idProperty = collection.idProperty, collectionData = utils.toNumeric(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf);
					for (const kpiDescription of kpisDescription){
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
					}
					const params = chartWidget.plotsToInclude ? JSON.parse(chartWidget.plotsToInclude)[0] : {}, 
						  plots =  {theSpider: {'type': 'Spider', labelOffset: params.labelOffset || -10, divisions:  params.divisions || 5, precision: precision, seriesFillAlpha: 0.1, seriesWidth: 2, markerSize: params.markerSiwe || 5,
						  						axisFont: params.axisFont || "normal normal normal 11pt Arial"}};
					let previousKpiValuesCache = {};
					for (const set of itemsSets){
						try{
							let setName = set.setName, filterString = setFilterString(set, expression, dateCol), kpiDate = expression.expressionToValue(set.kpidate), setCollection = collection, setData = collectionData, previousToDate, previousData = [];
							if (filterString){
								setCollection = collection.filter(expFilter.expressionToValue(filterString));
								setData = utils.toNumeric(setCollection.fetchSync(), grid);
								if (setData.length > 0){
									previousToDate = dutils.dateString(setData[setData.length - 1][dateCol], [-1, 'day']);
									previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
								}else{
									previousData = [];
								}
							}
							previousKpiValuesCache[setName] = {};
							let setExp = expressionEngine.expression(utils.toNumeric(setData, grid), idProperty, missingItemsKpis, valueOf, previousKpiValuesCache[setName], previousData, kpiDate);
							series[setName] = {value: {key: 'kpi', value: setName, tooltip: setName + 'Tooltip'}, 
								options: {plot: 'theSpider', fill: set.fillColor || 'black', hasFill: set.fill, stroke: {color: set.fillColor || 'black', style: set.kpimode === 'planned' ? 'shortDash' : ''}}};
							tableColumns[setName] = {label: hiutils.htmlToText(setName), field: setName, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}};
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
							Pmg.addFeedback(Pmg.message('erroritemsset') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('set') + ': ' + JSON.stringify(set));
						}
					}
					for (const set of itemsSets){
						let i = 0, setName = set.setName;
						for (const kpiDescription of kpisDescription){
							try{
								//const value = expKpi[setName][kpiDescription.name].expressionToValue(set.kpimode === 'planned' && kpiDescription.plannedkpicol ? "LAST('$' + kpiDescription.plannedkpicol)" : kpiDescription.kpi);
								const value = set.kpimode === 'planned' && kpiDescription.plannedkpicol && kpiDescription.plannedkpicol[0] !== '$'
									? kpiDescription.plannedkpicol 
									: expKpi[setName][kpiDescription.name].expressionToValue(set.kpimode === 'planned' && kpiDescription.plannedkpicol ? "LAST(kpiDescription.plannedkpicol)" : kpiDescription.kpi);
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

