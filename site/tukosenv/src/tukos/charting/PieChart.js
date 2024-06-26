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
			filterStrings.push(set.kpiFilter);
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
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter);
					let collection;
					if (chartWidget.chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					self.recursionDepth +=1;
					let kpisDescription = JSON.parse(chartWidget.kpisToInclude), kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, kpiFilters = {}, tableColumns = {name: {label: Pmg.message('name', form.object), field: 'name', renderCell: 'renderContent'}, value: {label: Pmg.message('value', form.object), field: 'value', renderCell: 'renderContent'}},
						idProperty = collection.idProperty, collectionData = utils.toNumeric(collection.fetchSync(), grid),
						expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf);
					const plots =  {thePie: {'type': 'Pie', labelOffset: -10}};
					let previousKpiValuesCache = {}, i = 0;
					series[0] = {value: {text: 'name', y: 'value', tooltip: 'tooltip'}, options: {plot: 'thePie'/*, fill: set.fillColor || 'black'*/}};
					for (const kpiDescription of kpisDescription){
						try{
							let filterString = setFilterString(kpiDescription, expression, dateCol), kpiDate = expression.expressionToValue(kpiDescription.kpidate), kpiCollection, previousToDate, previousData = [], expKpi;
							if (filterString){
								kpiCollection = collection.filter(expFilter.expressionToValue(filterString));
								kpiData = utils.toNumeric(kpiCollection.fetchSync(), grid);
								previousToDate = dutils.dateString(kpiData[kpiData.length - 1][dateCol], [-1, 'day']);
								previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
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
									chartData[i] = {name: subName, value: subValue[1], tooltip: subName + ': ' + (kpiDescription.displayformat ? utils.transform(subValue[1], kpiDescription.displayformat) : subValue[1]) + ' ' + (kpiDescription.tooltipunit || '')};
									i += 1;
								}
							}else{
								chartData[i] = {name: kpiDescription.name, value: value, tooltip: kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(value, kpiDescription.displayformat) : value) + ' ' + (kpiDescription.tooltipunit || '')};
								i += 1;
							}
						}catch(e){
							Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
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
								Pmg.addFeedback(Pmg.message('too many recursions') + ': ' + self.recursionDepth + ' (PieChart)');
								self.recursionDepth = 0;
								return;
							}
						    Pmg.serverDialog({action: 'Process', object: grid.object, view: 'edit', query: {programId: form.valueOf('id'), athleteid: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
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

