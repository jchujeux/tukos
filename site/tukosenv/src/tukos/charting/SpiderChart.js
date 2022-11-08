"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/expressionEngine", "tukos/PageManager"], 
function(declare, lang, utils, dutils, expressionFilter, expressionEngine, Pmg){
	const _getDate = function(dateInputString, form, selectedDateName){
		let date, dateString;
		switch(dateInputString){
			case 'ENDOFLASTWEEK': dateString = dutils.formatDate(date = dutils.dateAdd(dutils.getDayOfWeek(7, new Date()), 'day', -7)); break;
			case 'DISPLAYEDDAY': date = new Date(dateString = form.valueOf(selectedDateName)); break;
			case 'ENDOFDISPLAYEDWEEK': dateString = dutils.formatDate(date = dutils.getDayOfWeek(7, new Date(form.valueOf(selectedDateName)))); break;
			case 'TODAY': 
			default: 
				dateString = dutils.formatDate(date = new Date());
		}
		return {date: date, dateString: dateString};
	};
	const getDate = function(dateInputString, form, selectedDateName){
		if (dateInputString && dateInputString.includes('DATE(')){
			const match = dateInputString.match(/[(] *(\w+)[, ]*([+\-0-9]+)/), refDate = _getDate(match[1], form);
			const dayOrWeekMatch = match[1].match(/(DAY|WEEK)/), date = dutils.dateAdd(refDate.date, dayOrWeekMatch[1], Number(match[2])), dateString = dutils.formatDate(date);
			return {date: date, dateString: dateString};
		}else{
			return _getDate(dateInputString || 'today', form, selectedDateName);
		}
	};
	const setFilterString = function(set, form, dateCol, selectedDateName){
		let startDate, startDateString, filterStrings = [];
		const endDateObject = getDate(set.itemsUntil, form, selectedDateName), endDate = endDateObject.date, endDateString = endDateObject.dateString;
		let lastItemOffset;
		filterStrings.push('"' + dateCol + '" <= "' + endDateString + '"');
		if (set.durationOrSince){
			if (set.durationOrSince.includes('DURATION')){
				let match = set.durationOrSince.match(/[(] *(\w*)[, ]*(\d*)/);
				startDateString = dutils.formatDate(startDate = dutils.dateAdd(endDate, match[1], -Number(match[2])));
				filterStrings.push('"' + dateCol + '" > "' + startDateString + '"');
			}else if (set.durationOrSince.includes('LASTITEM')){//LASTITEM(0), LASTITEM(-1)
				let match = set.durationOrSince.match(/[(][ +-]*(\d*)/);
				lastItemOffset = (match && match[1]) || "0";
			}else{
				startDate = new Date(startDateString = set.durationOrSince);
				filterStrings.push('"' + dateCol + '" > "' + startDateString + '"');
			}
		}
		if (set.itemsFilter){
			filterStrings.push(set.itemsFilter);
		}
		return {filter: filterStrings.join(' AND '), days: startDate ? dutils.durationDays(startDate, endDate) : 0, lastItemOffset: lastItemOffset};
	};
	return declare(null, {
        constructor: function(args){
			lang.mixin(this, args);
        },
		setChartValue: function(chartWidgetName){
			var self = this, form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), kpiCache = {};
			if (!hidden && chartWidget.kpisToInclude){
				dojo.ready(function(){
					const grid = self.grid, dateCol = self.dateCol, filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter), collection = grid.collection.sort([{property: dateCol}, {property: 'rowId'}]);
					let kpisDescription = JSON.parse(chartWidget.kpisToInclude), itemsSets = (chartWidget.itemsSetsToInclude && JSON.parse(chartWidget.itemsSetsToInclude)) || [{setName: Pmg.message('allitemstodate')}], kpiData = {}, expKpi = {}, chartData = [], axes = {},
						series = {}, kpiFilters = {}, tableColumns = {kpi: {label: Pmg.message('kpi', 'sptprograms'), field: 'kpi', renderCell: 'renderContent'}};
					for (const kpiDescription of kpisDescription){
						const kpiName = kpiDescription.name + (kpiDescription.tooltipunit || '');
						chartData.push({kpi: kpiName});
						axes[kpiName] = {'type': 'Base', min: kpiDescription.axisMin || 0, max: kpiDescription.axisMax};
						if (kpiDescription.kpiFilter){
							kpiFilters[kpiName] = expFilter.expressionToValue(setFilterString(kpiDescription.kpiFilter, form, dateCol, self.selectedDate));
						}
						if (!kpiDescription.kpi.includes('$')){
							kpiDescription.kpi = '$' + kpiDescription.kpi;
						}
						if (!kpiDescription.kpi.match(/^\w+\(/)){
							kpiDescription.kpi = 'ITEM("' + kpiDescription.kpi + '", 0)';
						}
					}
					const plots =  {theSpider: {'type': 'Spider', labelOffset: -10, divisions:  4, precision: 0, seriesFillAlpha: 0.2, seriesWidth: 2}};
					for (const set of itemsSets){
						let setName = set.setName, filterDescription = setFilterString(set, form, dateCol, self.selectedDate), setFilter = expFilter.expressionToValue(filterDescription.filter), setCollection = collection.filter(setFilter),
							  setData = setCollection.fetchSync();
						if (filterDescription.lastItemOffset){
							setData = [setData[setData.length - 1 - filterDescription.lastItemOffset]];
						}
						let setExp = expressionEngine.expression(utils.toNumeric(setData, grid), kpiCache, setCollection.idProperty);
						series[setName] = {value: {key: 'kpi', value: setName}, options: {plot: 'theSpider', fill: set.fillColor || 'black'}};
						tableColumns[setName] = {label: setName, field: setName, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}};
						let setKpiData = (kpiData[setName] = {}), setExpKpi = (expKpi[setName] = {});
						for (const kpiDescription of kpisDescription){
							if (kpiDescription.kpiFilter){
								setKpiData[kpiDescription.kpiName] = utils.toNumeric(setCollection.filter(kpiFilters[kpiDescription.kpiName], grid).fetchSync());
								setExpKpi[kpiDescription.kpiName] = expressionEngine.expression(setKpiData[kpiDescription.kpiName], kpiCache, setCollection.idProperty);
							}else{
								setKpiData[kpiDescription.kpiName] = setData;
								setExpKpi[kpiDescription.kpiName] = setExp;
							}
						}
					}
					const computeKpis = function(){
						for (const set of itemsSets){
							let i = 0, setName = set.setName;
							for (const kpiDescription of kpisDescription){
								chartData[i][setName] = expKpi[setName][kpiDescription.kpiName].expressionToValue(kpiDescription.kpi);
								i += 1;
							}
						}
					};
					computeKpis();
					/*if (!utils.empty(kpiCache)){
					    let data = {}, idToIdg = {};
						utils.forEach(kpiCache, function(value, idp){
							let id = grid.store.getSync(idp).id;
							data[id] = kpiCache[idp];
							idToIdg[id] = idp;
						});
					    Pmg.serverDialog({action: 'Process', object: "sptitems", view: 'edit', query: {programId: form.valueOf('id'), athlete: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
					            function(response){
					           		const kpis = response.data.kpis;
									utils.forEach(kpis, function(kpi, id){
										let idp = idToIdg[id], itemKpis = kpis[id];
										utils.forEach(itemKpis, function(kpi, j){
											grid.updateDirty(idp, j, kpi);
										});
									});
									computeKpis();
									chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
					            },
					            function(error){
					                console.log('error:' + error);
					            }
					    );
					}else{*/
						chartWidget.set('value', {data: chartData, tableColumns: tableColumns, axes: axes, plots: plots, series: series});
					//}
				});
			}		  
		}
    });
}); 

