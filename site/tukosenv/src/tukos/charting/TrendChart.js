"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare,lang, utils, dutils, expressionFilter, expressionEngine, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
		setChartValue: function(chartWidgetName){
			console.log('trendChart.setChartValue: ' + chartWidgetName);
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {};
			if (!hidden  && chartWidget.kpisToInclude){
				dojo.ready(function(){
					let collection, horizontalAxisDescription;
					const grid = self.grid, dateCol = self.dateCol,
						 kpisDescription = JSON.parse(chartWidget.kpisToInclude), series = {}, chartData = [], tableData = [], axesDescription = JSON.parse(chartWidget.axesToInclude), axes = {},
						 plotsDescription = JSON.parse(chartWidget.plotsToInclude), plots = {}, tableColumns = {};
						
					axesDescription.forEach(function(axisDescription){
						axes[axisDescription.name] = axisDescription;
						if (!axisDescription.vertical){
							horizontalAxisDescription = axisDescription;
						}
					});
					const xType = horizontalAxisDescription.tickslabel || 'daysinceorigin', xTypePosition = ['daysinceorigin', 'dateofday', 'dayoftheyear', 'weeksinceorigin', 'dateofweek', 'weekoftheyear'].indexOf(xType), isWeek = xTypePosition > 2;
					tableColumns.id = {field: 'xType', label: Pmg.message(xType)};
					if (chartWidget.gridFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.gridFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						const data = utils.toNumeric(collectionData, grid), valueOf = form.valueOf.bind(form);//lang.hitch(form, form.valueOf);
						let firstDate = horizontalAxisDescription.firstdate, firstDateObject, lastDate = horizontalAxisDescription.lastdate, toDate, toDateObject, kpiCache = {}, previousKpiCache = {}, filter = collection.Filter(), xLabels = [],
							expression = expressionEngine.expression(collectionData, kpiCache, valueOf, previousKpiCache);
						firstDate = firstDate ? expression.expressionToValue(firstDate) : data[0][dateCol];
						if(!dutils.isValidDate(firstDate)){
							firstDate = dutils.formatDate(new Date());
						}
						firstDateObject = new Date(firstDate);
						lastDate = lastDate ? expression.expressionToValue(lastDate) : data[data.length - 1][dateCol];
						if(!dutils.isValidDate(lastDate)){
							lastDate = dutils.formatDate(new Date());
						}
						if (isWeek){
							firstDateObject = dutils.getDayOfWeek(1, firstDateObject);
							firstDate = dutils.formatDate(firstDateObject);
							toDateObject = dutils.getDayOfWeek(7, firstDateObject);
							toDate = dutils.formatDate(toDateObject);
						}else{
							firstDateObject = new Date(firstDate);
							toDate = firstDate
						}
						let previousToDate = dutils.dateString(firstDateObject, [-1, 'day']);
						horizontalAxisDescription.firstDate = firstDate;// needed by DynamicChart.getLabel
						plotsDescription.forEach(function(plotDescription){
							plots[plotDescription.name] = plotDescription;
							if (plotDescription.type === 'Indicator'){
								if (!plotDescription.lineStroke){
									plotDescription = lang.mixin(plotDescription, {stroke: null, outline: null, fill: null, labels: 'none', lineStroke: {color: 'red', style: 'shortDash', width: 2}, labelFunc: function(){
										return plotDescription.label || this.values;
									}});
								}
								if ((plotDescription.vertical !== false) && typeof (plotDescription.values) === 'string' && plotDescription.values[0] === '@'){
									plotDescription.values = dutils.difference(dutils.getDayOfWeek(1, firstDateObject), new Date(form.valueOf(plotDescription.values.substring(1))), isWeek ? 'week' : 'day') + 1;
								}
							}
						});
						let previousKpis = [];
						kpisDescription.forEach(function(kpiDescription, index){
							series[index] = {value: {y: index, tooltip: index + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
							tableColumns[index] = {field: index, label: kpiDescription.name}
							if (!kpiDescription.kpi.match(/^\w+\(/)){
								kpiDescription.kpi = 'ITEM("' + kpiDescription.kpi + '", 1)';
							}
						});
						let i = 1;
						const previousData = collection.filter(filter.lte(dateCol, previousToDate)).fetchSync();
						while (firstDate <= lastDate){
							xLabels[i] = self.xLabelValue(xType, firstDateObject, i);
							let chartItem = {id: xLabels[i]}, tableItem = {id: xLabels[i]};
							const periodData = collection.filter(filter.gte(dateCol, firstDate).lte(dateCol, toDate)).fetchSync();
							expression = expressionEngine.expression(periodData, kpiCache, valueOf, previousKpiCache, previousData, toDate);
							kpisDescription.forEach(function(kpiDescription, index){
								tableItem[index] = chartItem[index] = previousKpis[index] = expression.expressionToValue(kpiDescription.kpi);
								if (isNaN(chartItem[index]) && kpiDescription.absentiszero){
									chartItem[index] = 0;
								}
								chartItem[index + 'Tooltip'] = kpiDescription.name + ': ' + chartItem[index] + (kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit) + 
									(isWeek ? '<br><small>(' + Pmg.message('weekendingon', 'sptplans') + ' ' + dutils.formatDate(toDateObject, 'd MMM') + ')</small>' : '');
								if (kpiDescription.scalingfactor){
									chartItem[index] = chartItem[index] * kpiDescription.scalingfactor;
								}
							});
							tableData.push(tableItem);
							previousToDate = toDate;
							chartData.push(chartItem);
							i += 1;
							if (isWeek){
								firstDateObject.setDate(firstDateObject.getDate() + 7);
								firstDate = dutils.formatDate(firstDateObject);
								toDateObject.setDate(toDateObject.getDate() + 7);
								toDate = dutils.formatDate(toDateObject);
							}else{
								firstDateObject.setDate(firstDateObject.getDate() + 1);
								firstDate = toDate = dutils.formatDate(firstDateObject);
							}
						}
						if (horizontalAxisDescription.max && chartData.length > horizontalAxisDescription.max && horizontalAxisDescription.adjustmax){
							delete horizontalAxisDescription.max;
						}
						chartWidget.getLabel = function(options, formattedValue, rawValue){
							return xLabels[rawValue];
						}
					}
					if (!utils.empty(missingItemsKpis)){
					    let data = {}, idToIdg = {};
						utils.forEach(missingItemsKpis, function(value, idp){
							let id = grid.store.getSync(idp).id;
							data[id] = missingItemsKpis[idp];
							idToIdg[id] = idp;
						});
					    Pmg.serverDialog({action: 'Process', object: grid.object, view: 'edit', query: {programId: form.valueOf('id'), athlete: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
					            function(response){
					           		const kpis = response.data.kpis;
									utils.forEach(kpis, function(kpi, id){
										let idp = idToIdg[id], itemKpis = kpis[id];
										utils.forEach(itemKpis, function(kpi, j){
											grid.updateDirty(idp, j, kpi);
										});
									});
									self.setChartValue(chartWidgetName);
					            },
					            function(error){
					                console.log('error:' + error);
					            }
					    );
					}else{
						chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
					}
				});
			}		  
		},
		xLabelValue: function(xType, date, i){
			switch (xType){
				case 'dateofday': case 'dateofweek':
					return dutils.formatDate(date);
				case 'dayoftheyear':
					return dutils.getDayOfYear(date);
				case 'weekoftheyear':
					return dutils.getISOWeekOfYear(date);
				default:
					return i;
			}
		}
    });
}); 

