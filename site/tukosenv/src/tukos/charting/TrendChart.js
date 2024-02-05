"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare,lang, utils, dutils, expressionFilter, expressionEngine, Pmg){
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex, xLabels = [];
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
					tableColumns[0] = {field: 0, label: Pmg.message(xType)};
					if (chartWidget.chartFilter){
						collection = grid.collection.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const idProperty = collection.idProperty, collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						const data = utils.toNumeric(collectionData, grid), valueOf = form.valueOf.bind(form);//lang.hitch(form, form.valueOf);
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
							Pmg.addFeedback(Pmg.message('errorhorizontalaxis') + ': ' + e.message + ' - ' + Pmg.message('axisdescription') + ': ' + JSON.stringify(horizontalAxisDescription));
						}
						plotsDescription.forEach(function(plotDescription){
							try{
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
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorplotdefinition') + ': ' + e.message + ' - ' + Pmg.message('plotdescription') + ': ' + JSON.stringify(plotDescription));
							}
						});
						let previousKpis = [], index1;
						kpisDescription.forEach(function(kpiDescription, index){
							index1 = index + 1;
							series[index1] = {value: {y: index1, tooltip: index1 + 'Tooltip'}, options: {plot: kpiDescription.plot, label: kpiDescription.name, legend: kpiDescription.name}};
							tableColumns[index1] = {field: index1, label: kpiDescription.name}
							/*if (!kpiDescription.kpi.match(/^\w+\(/)){
								kpiDescription.kpi = 'ITEM("' + kpiDescription.kpi + '", 1)';
							}*/
						});
						let i = 1;
						const previousData = collection.filter(filter.lte(dateCol, previousToDate)).fetchSync();
						while (firstDate <= lastDate){
							xLabels[i] = self.xLabelValue(xType, firstDateObject, i);
							let chartItem = {}, tableItem = {0: xLabels[i]};
							const periodData = collection.filter(filter.gte(dateCol, firstDate).lte(dateCol, toDate)).fetchSync();
							expression = expressionEngine.expression(periodData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, toDate);
							kpisDescription.forEach(function(kpiDescription, index){
								try{
									index1 = index + 1
									tableItem[index1] = chartItem[index1] = previousKpis[index1] = expression.expressionToValue(kpiDescription.kpi);
									if (isNaN(chartItem[index1]) && kpiDescription.absentiszero){
										chartItem[index1] = 0;
									}
									chartItem[index1 + 'Tooltip'] = kpiDescription.name + ': ' + (kpiDescription.displayformat ? utils.transform(chartItem[index1], kpiDescription.displayformat) : chartItem[index1]) + ' ' + (kpiDescription.tooltipunit || '') + 
										(isWeek ? '<br><small>(' + Pmg.message('weekendingon', 'sptplans') + ' ' + dutils.formatDate(toDateObject, 'd MMM') + ')</small>' : '');
									if (kpiDescription.scalingfactor){
										chartItem[index1] = chartItem[index1] * kpiDescription.scalingfactor;
									}
								}catch(e){
									Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
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
								Pmg.addFeedback(Pmg.message('too many recursions') + ': ' + self.recursionDepth + ' (TrendChart)');
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
							chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
							self.recursionDepth = 0;
						}
					}else{
						chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
						self.recursionDepth = 0;
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

