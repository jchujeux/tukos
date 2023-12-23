"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare,lang, utils, dutils, expressionFilter, expressionEngine, Pmg){
	const setFilterString = function (description, expression, dateCol){
		const filterStrings = [];
		if (description.firstdate){
			filterStrings.push('"' + dateCol + '" >= "' + expression.expressionToValue(description.firstdate) + '"');
		}
		if (description.lastdate){
			filterStrings.push('"' + dateCol + '" <= "' + expression.expressionToValue(description.lastdate) + '"');
		}
		if (description.itemsFilter){
			filterStrings.push(description.itemsFilter);
		}
		return filterStrings.join(' AND ');
	};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex, xLabels = [];
			if (!hidden  && chartWidget.kpisToInclude){
				dojo.ready(function(){
					let collection, horizontalAxisDescription;
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter),
						 kpisDescription = JSON.parse(chartWidget.kpisToInclude), series = {}, chartData = [], tableData = [], axesDescription = JSON.parse(chartWidget.axesToInclude), axes = {},
						 plotsDescription = JSON.parse(chartWidget.plotsToInclude), plots = {}, tableColumns = {};
					axesDescription.forEach(function(axisDescription){
						axes[axisDescription.name] = axisDescription;
						if (!axisDescription.vertical){
							horizontalAxisDescription = axisDescription;
							axisDescription.labelFunc = function(textValue, rawValue){
								return xLabels[rawValue];
							}
						}
					});
					if (chartWidget.chartFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const idProperty = collection.idProperty, collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						const data = utils.toNumeric(collectionData, grid), valueOf = form.valueOf.bind(form);//lang.hitch(form, form.valueOf);
						let previousKpiValuesCache = {}, filter = collection.Filter(), expression = expressionEngine.expression(data, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache);
						plotsDescription.forEach(function(plotDescription){
							plots[plotDescription.name] = plotDescription;
						});
						let kpiIndex = 0, categoryIndex = 0, kpiNames = {}, categories = {};
						tableColumns[0] = {field: 0, label: Pmg.message('Category')};
						kpisDescription.forEach(function(kpiDescription, index){
							const name = kpiDescription.name, category = kpiDescription.category;
							if(typeof kpiNames[name] === "undefined"){
								kpiNames[name] = kpiIndex;
								series[kpiIndex] = {value: {y: kpiIndex, tooltip: kpiIndex + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
								kpiIndex += 1;
								tableColumns[kpiIndex] = {field: kpiIndex, label: kpiDescription.name};
							}
							if (typeof categories[category] === "undefined"){
								categories[category] = categoryIndex;
								chartData[categoryIndex] = {};
								tableData[categoryIndex] = {};
								categoryIndex += 1;
								xLabels[categoryIndex] = Pmg.message(category, grid.object);
							}
							kpiDescription.kpiIndex = kpiNames[name];
							kpiDescription.categoryIndex = categories[category];
						});
						kpisDescription.forEach(function(kpiDescription, index){
							try{
								let filterString = setFilterString(kpiDescription, expression, dateCol), kpiDate = expression.expressionToValue(kpiDescription.kpidate), kpiCollection = collection, kpiData = collectionData, previousToDate, previousData = [], kpiValue;
								if (filterString){
									kpiCollection = collection.filter(expFilter.expressionToValue(filterString));
									kpiData = utils.toNumeric(kpiCollection.fetchSync(), grid);
									previousToDate = dutils.dateString(kpiData[kpiData.length - 1][dateCol], [-1, 'day']);
									previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
								}
								expression = expressionEngine.expression(kpiData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
								tableData[kpiDescription.categoryIndex][kpiDescription.kpiIndex+1] = kpiValue = expression.expressionToValue(kpiDescription.kpi);
								if (isNaN(kpiValue) && kpiDescription.absentiszero){
									kpiValue = 0;
								}
								chartData[kpiDescription.categoryIndex][kpiDescription.kpiIndex + 'Tooltip'] = kpiDescription.name + ': ' + kpiValue + (kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit);
								if (kpiDescription.scalingfactor){
									kpiValue = kpiValue * kpiDescription.scalingfactor;
								}
								chartData[kpiDescription.categoryIndex][kpiDescription.kpiIndex] = kpiValue;
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
							}
						});
						if (horizontalAxisDescription.max && chartData.length > horizontalAxisDescription.max && horizontalAxisDescription.adjustmax){
							delete horizontalAxisDescription.max;
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
							chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
						}
					}else{
						chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
					}
				});
			}		  
		},
    });
}); 

