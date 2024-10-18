"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/charting/chartsUtils", "tukos/PageManager"], 
function(declare,lang, utils, dutils, expressionFilter, expressionEngine, chartsUtils, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, xLabels = [];
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
							axisDescription.labelFunc = function(textValue, rawValue){
								return xLabels[rawValue];
							}
						}
					});
					const xType = horizontalAxisDescription.tickslabel || 'daysinceorigin', xTypePosition = ['daysinceorigin', 'dateofday', 'dayoftheyear', 'weeksinceorigin', 'dateofweek', 'weekoftheyear'].indexOf(xType), isWeek = xTypePosition > 2;
					tableColumns[0] = {field: '0', label: Pmg.message(xType), rowsFilters: true};
					if (chartWidget.chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const idProperty = collection.idProperty, collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						self.recursionDepth +=1;
						const data = utils.toNumeric(collectionData, grid), valueOf = self.valueOf.bind(self);//lang.hitch(form, form.valueOf);
						let firstDate = horizontalAxisDescription.firstdate, firstDateObject, lastDate = horizontalAxisDescription.lastdate, toDate, toDateObject, previousKpiValuesCache = {}, filter = collection.Filter(),
							expression = expressionEngine.expression(collectionData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache), previousToDate;
						try{
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
							previousToDate = dutils.dateString(firstDateObject, [-1, 'day']);
							horizontalAxisDescription.firstDate = firstDate;// needed by DynamicChart.getLabel
						}catch(e){
							Pmg.addFeedback(Pmg.message('errorhorizontalaxis') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('axisdescription') + ': ' + JSON.stringify(horizontalAxisDescription));
						}
						plotsDescription.forEach(function(plotDescription){
							try{
								plots[plotDescription.name] = plotDescription;
								if (plotDescription.type === 'Indicator'){
									if (!plotDescription.lineStroke){
										plotDescription = lang.mixin(plotDescription, {stroke: null, outline: null, fill: null, labels: 'none', lineStroke: {color: plotDescription.indicatorColor || 'red', style: plotDescription.indicatorStyle || 'shortDash', width: 2},
											labelFunc: function(){
												return plotDescription.label || this.values;
											}
										});
									}
									if (/*(plotDescription.vertical !== false) && */typeof (plotDescription.values) === 'string' && plotDescription.values.indexOf('(') >= 0){
										plotDescription.values = dutils.difference(dutils.getDayOfWeek(1, firstDateObject), new Date(expression.expressionToValue(plotDescription.values)), isWeek ? 'week' : 'day') + 1;
									}else{
										plotDescription.values = Number(plotDescription.values);
									}
								}
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorplotdefinition') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('plotdescription') + ': ' + JSON.stringify(plotDescription));
							}
						});
						let previousKpis = [], index1;
						kpisDescription.forEach(function(kpiDescription, index){
							if (kpiDescription.plot){
								index1 = index + 1;
								series[index1] = {value: {y: index1, tooltip: index1 + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
								tableColumns[index1] = {field: index1, label: kpiDescription.name, rowsFilters: true}
							}
						});
						let i = 1;
						const previousData = collection.filter(filter.lte(dateCol, previousToDate)).fetchSync();
						while (firstDate <= lastDate){
							xLabels[i] = self.xLabelValue(xType, firstDateObject, i);
							let chartItem = {}, tableItem = {id: i, 0: xLabels[i]};
							const periodData = collection.filter(filter.gte(dateCol, firstDate).lte(dateCol, toDate)).fetchSync();
							expression = expressionEngine.expression(periodData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, toDate);
							kpisDescription.forEach(function(kpiDescription, index){
								if (kpiDescription.plot){
									try{
										index1 = index + 1
										tableItem[index1] = chartItem[index1] = previousKpis[index1] = expression.expressionToValue(kpiDescription.kpi);
										if (isNaN(chartItem[index1]) && kpiDescription.absentiszero){
											chartItem[index1] = 0;
										}
										chartItem[index1 + 'Tooltip'] = kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(chartItem[index1], kpiDescription.displayformat) : chartItem[index1]) + ' ' + (kpiDescription.tooltipunit || '') + 
											(isWeek 
												? '<br><small>(' + Pmg.message('weekendingon', 'sptplans') + ' ' + dutils.formatDate(toDateObject, 'd MMM') + ')</small>' 
												: '<br><small>(' + dutils.formatDate(firstDateObject, 'd MMM') + ')</small>');
										if (kpiDescription.scalingfactor){
											chartItem[index1] = chartItem[index1] * kpiDescription.scalingfactor;
										}
									}catch(e){
										Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
									}
								}
							});
							chartData.push(chartItem);
							if (! chartWidget.tableSkipEmptyPeriods || periodData.length > 0){
								tableData.push(tableItem);
							}
							previousToDate = toDate;
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
					}
					chartsUtils.processMissingKpis(missingItemsKpis, grid, self, chartWidgetName, chartData, tableData, tableColumns, axes, plots, series);
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

