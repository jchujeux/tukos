"use strict";
define(["dojo/_base/declare", "dojo/_base/lang",  "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare,lang,   ArrayIterator, utils, dutils, expressionFilter, expressionEngine, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), kpiCache = {};
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
					plotsDescription.forEach(function(plotDescription){
						plots[plotDescription.name] = plotDescription;
						if (plotDescription.type === 'Indicator' && !plotDescription.lineStroke){
							plotDescription = lang.mixin(plotDescription, {stroke: null, outline: null, fill: null, labels: 'none', lineStroke: {color: 'red', style: 'shortDash', width: 2}, labelFunc: function(){
								return plotDescription.label || this.values;
							}});
						}
					});
					const xType = horizontalAxisDescription.tickslabel || 'daysinceorigin', xTypePosition = ['daysinceorigin', 'dateofday', 'dayoftheyear', 'weeksinceorigin', 'dateofweek', 'weekoftheyear'].indexOf(xType), isWeek = xTypePosition > 2;
					tableColumns.id = {field: 'xType', label: Pmg.message(xType)};
					kpisDescription.forEach(function(kpiDescription, index){
						series[index] = {value: {y: index, tooltip: index + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
						tableColumns[index] = {field: index, label: kpiDescription.name}
						if (!kpiDescription.kpi.includes('$')){
							kpiDescription.kpi = '$' + kpiDescription.kpi;
						}
						if (!kpiDescription.kpi.match(/^\w+\(/)){
							kpiDescription.kpi = 'ITEM("' + kpiDescription.kpi + '", 0)';
						}
					});
					if (chartWidget.gridFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.gridFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						const data = utils.toNumeric(collectionData, grid), toDate = data[data.length - 1][dateCol], kpiCache = {}, filter = collection.Filter(), xLabels = [];
						let firstDate = (horizontalAxisDescription.dateoforigin && form.valueOf(horizontalAxisDescription.dateoforigin)) || data[0][dateCol], firstDateObject = new Date(firstDate), lastDate, lastDateObject;
						if (isWeek){
							firstDateObject = dutils.getDayOfWeek(1, firstDateObject);
							firstDate = dutils.formatDate(firstDateObject);
							lastDateObject = dutils.getDayOfWeek(7, firstDateObject);
							lastDate = dutils.formatDate(lastDateObject);
						}else{
							firstDateObject = new Date(firstDate);
							lastDate = firstDate
						}
						horizontalAxisDescription.firstDate = firstDate;// needed by DynamicChart.getLabel
						let i = 1;
						while (firstDate <= toDate){
							xLabels[i] = self.xLabelValue(xType, firstDateObject, i);
							let chartItem = {id: xLabels[i]}, tableItem = {id: xLabels[i]};
							const periodData = collection.filter(filter.gte(dateCol, firstDate).lte(dateCol, lastDate)).fetchSync();
							if (periodData.length > 0){
								const expression = expressionEngine.expression(periodData, kpiCache, collection.idProperty);
								kpisDescription.forEach(function(kpiDescription, index){
									tableItem[index] = chartItem[index] = expression.expressionToValue(kpiDescription.kpi);
									if (isNaN(chartItem[index]) && kpiDescription.absentiszero){
										chartItem[index] = 0;
									}
									chartItem[index + 'Tooltip'] = kpiDescription.name + ': ' + chartItem[index] + (kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit);
									if (kpiDescription.scalingfactor){
										chartItem[index] = chartItem[index] * kpiDescription.scalingfactor;
									}
								});
								tableData.push(tableItem);
							}else{
								kpisDescription.forEach(function(kpiDescription, index){
									if (kpiDescription.absentiszero){
										chartItem[index] = 0;
										chartItem[index + 'Tooltip'] = kpiDescription.name + ': ' + chartItem[index];
									}else{
										chartItem[index] = null;
									}
								});
							}
							chartData.push(chartItem);
							i += 1;
							if (isWeek){
								firstDateObject.setDate(firstDateObject.getDate() + 7);
								firstDate = dutils.formatDate(firstDateObject);
								lastDateObject.setDate(lastDateObject.getDate() + 7);
								lastDate = dutils.formatDate(lastDateObject);
							}else{
								firstDateObject.setDate(firstDateObject.getDate() + 1);
								firstDate = lastDate = dutils.formatDate(firstDateObject);
							}
						}
						if (horizontalAxisDescription.max && chartData.length > horizontalAxisDescription.max && horizontalAxisDescription.adjustmax){
							delete horizontalAxisDescription.max;
						}
						chartWidget.getLabel = function(options, formattedValue, rawValue){
							return xLabels[rawValue];
						}
					}
					chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
				});
			}		  
		},
		xLabelValue: function(xType, date, i){
			switch (xType){
				case 'dateofday': case 'dateofweek':
					return dutils.formatDate(date);
					break;
				case 'dayoftheyear':
					return dutils.getDayOfYear(date);
					break;
				case 'weekoftheyear':
					return dutils.getISOWeekOfYear(date);
					break;
				default:
					return i;
			}
		}
    });
}); 

