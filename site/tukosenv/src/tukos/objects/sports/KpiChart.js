"use strict";
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils", "tukos/dstore/expressionFilter", "tukos/objects/sports/expressionKpi", "tukos/PageManager"], 
function(declare, lang, ArrayIterator, utils, dutils, expressionFilter, expressionKpi, Pmg){
	const _getDate = function(dateInputString, form){
		let date, dateString;
		switch(dateInputString){
			case 'ENDOFLASTWEEK': dateString = dutils.formatDate(date = dutils.dateAdd(dutils.getDayOfWeek(7, new Date()), 'day', -7)); break;
			case 'DISPLAYEDDAY': date = new Date(dateString = form.valueOf('displayeddate')); break;
			case 'ENDOFDISPLAYEDWEEK': dateString = dutils.formatDate(date = dutils.getDayOfWeek(7, new Date(form.valueOf('displayeddate')))); break;
			case 'TODAY': 
			default: 
				dateString = dutils.formatDate(date = new Date());
		}
		return {date: date, dateString: dateString};
	};
	const getDate = function(dateInputString, form){
		if (dateInputString && dateInputString.includes('DATE(')){
			const match = dateInputString.match(/[(] *(\w+)[, ]*([+\-0-9]+)/), refDate = _getDate(match[1], form);
			const dayOrWeekMatch = match[1].match(/(DAY|WEEK)/), date = dutils.dateAdd(refDate.date, dayOrWeekMatch[1], Number(match[2])), dateString = dutils.formatDate(date);
			return {date: date, dateString: dateString};
		}else{
			return _getDate(dateInputString || 'today', form);
		}
	};
	const setFilterString = function(set, form){
		let startDate, startDateString, filterStrings = [];
		if (set.sessionsUntil){
			const endDateObject = getDate(set.sessionsUntil, form), endDate = endDateObject.date, endDateString = endDateObject.dateString;
			let lastSessionOffset;
			filterStrings.push('"startdate" <= "' + endDateString + '"');
			if (set.durationOrSince){
				if (set.durationOrSince.includes('DURATION')){
					let match = set.durationOrSince.match(/[(] *(\w*)[, ]*(\d*)/);
					startDateString = dutils.formatDate(startDate = dutils.dateAdd(endDate, match[1], -Number(match[2])));
					filterStrings.push('"startdate" > "' + startDateString + '"');
				}else if (set.durationOrSince.includes('LASTSESSION')){//LASTSESSION(0), LASTSESSION(-1)
					let match = set.durationOrSince.match(/[(][ +-]*(\d*)/);
					lastSessionOffset = match[1];
				}else{
					startDate = new Date(startDateString = set.durationOrSince);
					filterStrings.push('"startdate" > "' + startDateString + '"');
				}
			}
			if (set.mode){
				filterStrings.push('"mode" = "' + set.mode + '"');
			}
			if (set.sessionsFilter){
				filterStrings.push(set.sessionsFilter);
			}
			return {filter: filterStrings.join(' AND '), days: startDate ? dutils.durationDays(startDate, endDate) : 0, lastSessionOffset: lastSessionOffset};
		}
	};
	return declare(null, {
        constructor: function(args){
			this.sessionsStore = args.sessionsStore;
			this.form = args.form;
			this.collection = this.sessionsStore.sort([{property: 'startdate'}, {property: 'sessionid'}]);
			this.sessionsIterator = new ArrayIterator();
        },
		setChartValue: function(chartWidgetName){
			var form = this.form, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), kpiCache = {};
			if (!hidden && chartWidget.sessionsSetsToInclude && chartWidget.kpisToInclude){
				dojo.ready(function(){
					var grid = form.getWidget('sptsessions'), filter = new grid.store.Filter(), expFilter = expressionFilter.expression(filter);
					let kpis = JSON.parse(chartWidget.kpisToInclude), sessionsSets = JSON.parse(chartWidget.sessionsSetsToInclude), kpiData = {}, expKpi = {}, chartData = [], axes = {}, series = {}, kpiFilters = {}, 
						tableColumns = {kpi: {label: Pmg.message('kpi', 'sptprograms'), field: 'kpi', renderCell: 'renderContent'}};
					for (const kpi of kpis){
						const kpiName = kpi.kpiName;
						chartData.push({kpi: kpiName});
						axes[kpiName] = {'type': 'Base', min: kpi.axisMin || 0, max: kpi.axisMax};
						if (kpi.kpiFilter){
							kpiFilters[kpiName] = expFilter.expressionToValue(setFilterString(kpi.kpiFilter, form));
						}
					}
					for (const set of sessionsSets){
						const setName = set.setName, kpiDate = getDate(set.kpiDate, form).dateString, filterString = setFilterString(set, form), setFilter = expFilter.expressionToValue(filterString.filter),
							  setCollection = grid.collection.filter(setFilter), setData = setCollection.fetchSync(), setExp = expressionKpi.expression(kpiDate, filterString.days, setData, filterString.lastSessionOffset,
							  kpiCache, setCollection.idProperty);
						series[setName] = {value: {key: 'kpi', value: setName}, options: {plot: 'theSpider', fill: set.fillColor}};
						tableColumns[setName] = {label: setName, field: setName, renderCell: 'renderContent', formatType: 'number', formatOptions: {places: 1}};
						let setKpiData = (kpiData[setName] = {}), setExpKpi = (expKpi[setName] = {});
						for (const kpi of kpis){
							if (kpi.kpiFilter){
								setKpiData[kpi.kpiName] = setCollection.filter(kpiFilters[kpi.kpiName]).fetchSync();
								setExpKpi[kpi.kpiName] = expressionKpi.expression(kpiDate, filterString.days, setKpiData[kpi.kpiName], filterString.lastSessionOffset, kpiCache, setCollection.idProperty);
							}else{
								setKpiData[kpi.kpiName] = setData;
								setExpKpi[kpi.kpiName] = setExp;
							}
						}
					}
					const computeKpis = function(){
						for (const set of sessionsSets){
							let i = 0, setName = set.setName;
							for (const kpi of kpis){
								chartData[i][setName] = expKpi[setName][kpi.kpiName].expressionToValue(kpi.formula);
								i += 1;
							}
						}
					};
					computeKpis();
					if (!utils.empty(kpiCache)){
					    let data = {}, idToIdg = {};
						utils.forEach(kpiCache, function(value, idp){
							let id = grid.store.getSync(idp).id;
							data[id] = kpiCache[idp];
							idToIdg[id] = idp;
						});
					    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {programId: form.valueOf('id'), athlete: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}}}, {data: data}).then(
					            function(response){
					           		const kpis = response.data.kpis;
									utils.forEach(kpis, function(kpi, id){
										let idp = idToIdg[id], sessionKpis = kpis[id];
										utils.forEach(sessionKpis, function(kpi, j){
											grid.updateDirty(idp, j, kpi);
										});
									});
									computeKpis();
									chartWidget.set('value', {data: chartData, tableColumns: tableColumns, resetAxes: true, axes: axes, resetSeries: true, series: series});
					            },
					            function(error){
					                console.log('error:' + error);
					            }
					    );
					}else{
						chartWidget.set('value', {data: chartData, tableColumns: tableColumns, resetAxes: true, axes: axes, resetSeries: true, series: series});
					}
				});
			}		  
		},
		setDisplayedDateChartsValue: function(){
			const form = this.form;
			if (form.programsConfig && form.programsConfig.spiders){
				const spiders = JSON.parse(form.programsConfig.spiders);
				for (const spider of spiders){
					const chartWidgetName = 'spider' + spider.id, chartWidget = form.getWidget(chartWidgetName);
					if (chartWidget.sessionsSetsToInclude.includes('DISPLAYED')){
						this.setChartValue(chartWidgetName);
					}
				}
			}
		}
    });
}); 

