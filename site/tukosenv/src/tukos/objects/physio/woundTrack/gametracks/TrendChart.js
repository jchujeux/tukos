"use strict";
define(["dojo/_base/declare", "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/objects/physio/woundTrack/gametracks/expressionTrend", "tukos/PageManager"], 
function(declare, ArrayIterator, utils, dutils, expressionFilter, expressionTrend, Pmg){
	return declare(null, {
        constructor: function(args){
			this.grid = args.grid;
			this.dateCol = args.dateCol;
			this.form = args.form;
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
					});
					tableColumns.day = {field: 'day', label: 'Jour'}
					kpisDescription.forEach(function(kpiDescription, index){
						series[index] = {value: {y: index, tooltip: index + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
						tableColumns[index] = {field: index, label: kpiDescription.name}
						if (!kpiDescription.kpi.includes('$')){
							kpiDescription.kpi = '$' + kpiDescription.kpi;
						}
						/*if (!kpiDescription.kpi.includes('(')){
							kpiDescription.kpi = 'SESSION("' + kpiDescription.kpi + '", @dataIndex)';
						}*/
						if (!kpiDescription.kpi.match(/^\w+\(/)){
							kpiDescription.kpi = 'SESSION("' + kpiDescription.kpi + '", @dataIndex)';
						}
					});
					if (chartWidget.gridFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.gridFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const data = collection.fetchSync(), toDate = data[data.length - 1][dateCol], kpiCache = {}, dayType = horizontalAxisDescription.tickslabel || 'dayoftreatment',
						  expTrend = expressionTrend.expression(data, kpiCache, collection.idProperty);
					let dayDate, dayDateObject, i = 1, dataIndex = 0;//dayDate = data[0][dateCol], dayDateObject = dutils.parseDate(dayDate), i = 1, dataIndex = 0;
					switch (horizontalAxisDescription.dateoforigin){
						case 'woundstartdate':
						case 'treatmentstartdate': dayDate = form.valueOf(horizontalAxisDescription.dateoforigin) || data[0][dateCol]; break;
						default: dayDate = data[0][dateCol];
					}
					dayDateObject = dutils.parseDate(dayDate)
					horizontalAxisDescription.firstDate = dayDate;
					while (dayDate <= toDate){
						let chartItem = {}, tableItem = {}, item = data[dataIndex] || {};
						chartItem = {day: dayType === 'dayoftreatment' ? i : dayDate};
						tableItem = {day: dayType === 'dayoftreatment' ? i : dayDate};
						if (item[dateCol] === dayDate){
							kpisDescription.forEach(function(kpiDescription, index){
								tableItem[index] = chartItem[index] = expTrend.expressionToValue(kpiDescription.kpi.replace('@dataIndex', dataIndex));//item[kpiDescription.kpi];
								if (isNaN(chartItem[index]) && kpiDescription.absentiszero){
									chartItem[index] = 0;
								}
								chartItem[index + 'Tooltip'] = kpiDescription.name + ': ' + chartItem[index] + (kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit);
								if (kpiDescription.scalingfactor){
									chartItem[index] = chartItem[index] * kpiDescription.scalingfactor;
								}
							});
							dataIndex += 1;
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
						dayDateObject.setDate(dayDateObject.getDate() + 1);
						dayDate = dutils.formatDate(dayDateObject);
					}
					if (horizontalAxisDescription.max && chartData.length > horizontalAxisDescription.max && horizontalAxisDescription.adjustmax){
						delete horizontalAxisDescription.max;
					}
					chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
				});
			}		  
		}
    });
}); 

