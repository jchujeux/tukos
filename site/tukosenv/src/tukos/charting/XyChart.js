"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "dojo/_base/Color", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare,lang, Color, utils, dutils, expressionFilter, expressionEngine, Pmg){
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
        postCreate: function(){
			this.recursionDepth = 0;
		},
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), missingItemsKpis = {}, missingKpisIndex = chartWidget.missingKpisIndex;
			if (!hidden  && chartWidget.kpisToInclude){
				dojo.ready(function(){
					let collection, log10;
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter),
						 kpisDescription = JSON.parse(chartWidget.kpisToInclude), series = {}, chartData = [], tableColumns = [], tableData = [{}], axesDescription = JSON.parse(chartWidget.axesToInclude), axes = {},
						 plotsDescription = JSON.parse(chartWidget.plotsToInclude), plots = {};
					axesDescription.forEach(function(axisDescription){
						axes[axisDescription.name] = axisDescription;
						if (axisDescription.scaletype){
							log10 = Math.log(10);
							axisDescription.natural = true;
							axisDescription.includeZero = true;
							axisDescription.labelFunc = function(textValue, rawValue){
								return ["1", "10", "100", "10^3", "10^4", "10^5", "10^7", "10^8", "10^9"][rawValue];
							}
							axisDescription.isLogarithmic = true;
						}
					});
					if (chartWidget.chartFilter){
						collection = grid.store.filter(expressionFilter.expression((new grid.store.Filter())).expressionToValue(chartWidget.chartFilter)).sort([{property: dateCol}, {property: 'rowId'}]);
					}else{
						collection = grid.store.sort([{property: dateCol}, {property: 'rowId'}]);
					}
					const idProperty = collection.idProperty, collectionData = collection.fetchSync();
					if (collectionData.length > 1){
						self.recursionDepth +=1;
						const data = utils.toNumeric(collectionData, grid), valueOf = self.valueOf.bind(self);//lang.hitch(form, form.valueOf);
						let previousKpiValuesCache = {}, filter = collection.Filter(), expression = expressionEngine.expression(data, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache);
						plotsDescription.forEach(function(plotDescription){
							plots[plotDescription.name] = plotDescription;
							if (plotDescription.markersProgressColor){
								plotDescription.styleFunc = function(item){
									return item.fill ? {fill: item.fill, stroke:{color: item.fill, width: 2}} : {};
								}
							}
							if (plotDescription.type === 'Indicator'){
								if (!plotDescription.lineStroke){
									plotDescription = lang.mixin(plotDescription, {stroke: null, outline: null, fill: null, labels: 'none', lineStroke: {color: 'red', style: 'shortDash', width: 2}, 
											offset: {x:plotDescription.xlabeloffset || 0, y: plotDescription.ylabeloffset || 0}, labelFunc: function(){
										return plotDescription.label || this.values;
									}});
								}
								plotDescription.values = Number(plotDescription.values);
							}
						});
						const adjustTableAndChartToXYlength = function(xyArray){
							const missingRows = xyArray.length - chartData.length;
							if (missingRows > 0){
								for (let i = 0; i < missingRows; i++){
									tableData.push({});
									chartData.push({});
								}
							}
						};
						const white = Color.fromString('white'), yellow = Color.fromString('yellow'), orange = Color.fromString('orange'), red = Color.fromString('red'), darkRed = Color.fromString('darkred');
						kpisDescription.forEach(function(kpiDescription){
							try{
								let filterString = setFilterString(kpiDescription, expression, dateCol), kpiDate = expression.expressionToValue(kpiDescription.kpidate), kpiCollection = collection, kpiData = collectionData, previousToDate, previousData = [];
								if (filterString){
									kpiCollection = collection.filter(expFilter.expressionToValue(filterString));
									kpiData = utils.toNumeric(kpiCollection.fetchSync(), grid);
									previousToDate = dutils.dateString(kpiData[kpiData.length - 1][dateCol], [-1, 'day']);
									previousData = utils.toNumeric(collection.filter(filter.lte(dateCol, previousToDate)).fetchSync(), grid);
									expression = expressionEngine.expression(kpiData, idProperty, missingItemsKpis, valueOf, previousKpiValuesCache, previousData, kpiDate);
								}
								const name = kpiDescription.name, xName = name + ' (x)', yName = name + ' (y)', zName = name + ' (z)', tooltipName = name + 'Tooltip', fillName = name + 'Fill', plotName = kpiDescription.plot, 
										plot = plots[plotName], xAxis = plot.hAxis, yAxis = plot.vAxis, xAxisIsLogarithmic = axes[xAxis].isLogarithmic, yAxisIsLogarithmic = axes[yAxis].isLogarithmic;
								tableColumns.push({field: xName, label: xName}, {field: yName, label: yName});
								series[name] = {value: {x: xName, y: yName, tooltip: tooltipName}, kwArgs: {query: function(item){return item[xName] !== undefined;}}, options: {plot: plotName, label: name, legend: name}};
								if (plot.type === 'Bubble'){
									series[name].value.size = zName;
								}
								if (plot.markersProgressColor){
									series[name].value.fill = fillName;
								}
								const xyValues = expression.expressionToValue(kpiDescription.kpi), xyLength = xyValues.length;
								adjustTableAndChartToXYlength(xyValues);
								
								xyValues.forEach && xyValues.forEach(function(xyValue, index){
									const index1 = index + 1;
									tableData[index1][xName] = chartData[index][xName] = xyValue[0];
									tableData[index1][yName] = chartData[index][yName] = xyValue[1];
									if (plot.type === 'Bubble'){
										tableData[index1][zName] = chartData[index][zName] = xyValue[2];
									}
									chartData[index][tooltipName] = name + ': {' + utils.transform(xyValue[0], kpiDescription.xdisplayformat) + ', ' + utils.transform(xyValue[1], kpiDescription.ydisplayformat) + (xyValue[2] ? '; ' + xyValue[2] : '') + '}' + 
										(kpiDescription.tooltipunit === undefined ? '' :  kpiDescription.tooltipunit);
									if (plot.markersProgressColor){
										const colorRatio = index / xyLength;
										chartData[index][fillName] = colorRatio <= 0.25 ? Color.blendColors(white, yellow, colorRatio).toHex() : (colorRatio <= 0.5 ? Color.blendColors(yellow, orange, colorRatio).toHex() : 
											(colorRatio <= 0.75 ? Color.blendColors(orange, red, colorRatio).toHex() : Color.blendColors(red, darkRed, colorRatio).toHex()));
									}
									if (xAxisIsLogarithmic){
										chartData[index][xName] = Math.log(xyValue[0]) / log10;
									}
									if (yAxisIsLogarithmic){
										chartData[index][yName] = Math.log(xyValue[1]) / log10;
									}
								});
							}catch(e){
								Pmg.addFeedback(Pmg.message('errorkpieval') + ': ' + e.message + ' - ' + Pmg.message('chart') + ': ' + chartWidget.title + ' - ' + Pmg.message('kpi') + ': ' + kpiDescription.name);
							}
						});
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
								Pmg.addFeedback(Pmg.message('too many recursions') + ': self.recursionDepth (XyChart)');
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
    });
}); 

