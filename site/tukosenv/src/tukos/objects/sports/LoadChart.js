define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils", "tukos/PageManager"], 
function(declare, lang, when, ArrayIterator, utils, dutils, Pmg){
return declare(null, {
        constructor: function(args){
			this.sessionsStore = (args.sessionsStore || args.workoutsStore);
			this.sessionsWidgetName = args.sessionsStore ? 'sptsessions' : 'sptworkouts';
			this.collection = this.sessionsStore.sort([{property: 'startdate'}, {property: 'sessionid'}]);
			this.sessionsIterator = new ArrayIterator();
        },
		setWeekLoadChartValue: function(form, chartWidgetName){
			var self = this, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden');
			if (!hidden){
				dojo.ready(function(){
					var chartAtts = chartWidget.get('chartAtts'), date = new Date(form.valueOf('displayeddate')), dayDate, chartItem, tableItem, chartData = [],
					    tableData = [], grid = form.getWidget(self.sessionsWidgetName), dayType = chartWidget.get('daytype'), filter = new grid.store.Filter(), presentCols = [], colsTLabel = {}, series = chartWidget.get('series'), 
						sessionsFilter = chartAtts.filter, previousSession, stsDailyDecay = grid.tsbCalculator.get('stsDailyDecay'), ltsDailyDecay = grid.tsbCalculator.get('ltsDailyDecay'), 
						stsRatio = grid.tsbCalculator.get('stsRatio'), hasSession, hasPMC;
					utils.forEach(series, function(content, col){
						presentCols.push(col);
						colsTLabel[col] = content.options.label;
					});
					hasPMC = presentCols.indexOf('tsb') !== -1;
					for (let i = 1; i <= 7; i++){
						hasSession = false;	  	
						dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));
					  	chartItem = {day: dayType === 'dayofweek' ? dutils.dayName(i) : dayDate};
						tableItem = lang.clone(chartItem);
				      	presentCols.forEach(function(col){
				        	chartItem[col] = 0;
				      	});
						self.collection.filter(filter.eq('startdate', dayDate)[sessionsFilter]('mode', 'performed')).forEach(function(session){
							if (session.sport !== 'rest'){
								hasSession = true;
								self.buildChartItem(chartItem, session, presentCols, chartAtts, true);
								previousSession = session;
					        }
						});
						if (!hasSession){
							//find first session anterior to this day's date
							if (!previousSession){
								when(self.collection.filter(filter.gt('startdate', '')[sessionsFilter]('mode', 'performed')).fetchSync(), function(data){
									previousSession = self.sessionsIterator.initialize(data, 'last');
									while (previousSession && previousSession.startdate && (previousSession.startdate  > dayDate)){
										previousSession = self.sessionsIterator.previous();
									}
									if (!previousSession){
										previousSession = {startdate: form.valueOf('fromdate'), sts: grid.tsbCalculator.get('initialSts'), lts: grid.tsbCalculator.get('initialLts')};
									}
								});
							}
							if (hasPMC){
								self.buildPmcCols(chartItem, previousSession, dayDate, stsDailyDecay, ltsDailyDecay, stsRatio);
							}
						}
						self.finalizeChartItem(chartWidget, chartItem, tableItem, presentCols, chartAtts, colsTLabel, 'day');
						chartData.push(chartItem);
						tableData.push(tableItem);
					}
					chartWidget.set('value', {data: chartData, tableData: tableData, axes: {x: {title: Pmg.message(dayType, 'sptprograms')}}});
				});
			}		  
		},
		setProgramLoadChartValue: function(form, chartWidgetName){
			var self = this, chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden');
			if (!hidden){
				dojo.ready(function(){
					var chartWidget = form.getWidget(chartWidgetName), chartAtts = chartWidget.get('chartAtts'), fromDateS = form.valueOf('displayfromdate') || form.valueOf('fromdate'), toDateS = form.valueOf('todate'), chartItem, tableItem, 
						chartData = [], tableData = [], grid = form.getWidget(self.sessionsWidgetName), weekType = chartWidget.get('weektype'), filter = new grid.store.Filter(), presentCols = [], colsTLabel = {},
						series = chartWidget.get('series'), sessionsFilter = chartAtts.filter, stsDailyDecay = grid.tsbCalculator.get('stsDailyDecay'), ltsDailyDecay = grid.tsbCalculator.get('ltsDailyDecay'), 
						stsRatio = grid.tsbCalculator.get('stsRatio'), fromDate, weekNumber = 0,
						previousSession = {startdate: fromDateS, sts: grid.tsbCalculator.get('initialSts'), lts: grid.tsbCalculator.get('initialLts')}, mondayDate, mondayDateS, 
						sundayDate, sundayDateS, hasPMC;
					utils.forEach(series, function(content, col){
						presentCols.push(col);
						colsTLabel[col] = content.options.label;
					});
					hasPMC = presentCols.indexOf('tsb') !== -1;
					mondayDate = dutils.getDayOfWeek(1, fromDate = new Date(fromDateS));
					sundayDate = dutils.getDayOfWeek(7, fromDate);
					mondayDateS = dutils.formatDate(mondayDate);
					sundayDateS = dutils.formatDate(sundayDate);
					while (sundayDateS <= toDateS){
						weekNumber += 1;
	                    chartItem = {id: weekNumber, week: Pmg.message('w', 'sptprograms') + (weekType == 'weekofprogram' ? weekNumber : dutils.getISOWeekOfYear(mondayDate))/*, weekof: mondayDate*/};
						tableItem = lang.clone(chartItem);
				      	presentCols.forEach(function(col){
				        	chartItem[col] = 0;
				      	});
						self.collection.filter(filter.gte('startdate', mondayDateS).lte('startdate', sundayDateS)[sessionsFilter]('mode', 'performed')).forEach(function(session){
							if (session.sport !== 'rest'){
								self.buildChartItem(chartItem, session, presentCols, chartAtts);
								previousSession = session;
							}
						});
						if (hasPMC && (previousSession.startdate < sundayDateS)){
							self.buildPmcCols(chartItem, previousSession, sundayDateS, stsDailyDecay, ltsDailyDecay, stsRatio);
						}
						self.finalizeChartItem(chartWidget, chartItem, tableItem, presentCols, chartAtts, colsTLabel, 'week', '<br><small>(' + Pmg.message('weekendingon', 'sptprograms') + ' ' + dutils.formatDate(sundayDate, 'd MMM') + ')</small>');
						chartData.push(chartItem);
						tableData.push(tableItem);
						mondayDate = dutils.dateAdd(mondayDate, 'week', 1);
						sundayDate = dutils.dateAdd(sundayDate, 'week', 1);
						mondayDateS = dutils.formatDate(mondayDate);
						sundayDateS = dutils.formatDate(sundayDate);
					}
					chartWidget.set('value', {data: chartData, tableData: tableData, axes: {x: {title: Pmg.message(weekType, 'sptprograms')}}});
				});
			}
		},
		buildChartItem: function(chartItem, session, presentCols, chartAtts, week){
			if (session.duration){
				var self = this, duration = dutils.seconds(session.duration, 'time') / 60;          
				presentCols.forEach(function(col){
	                var colAtts = chartAtts.cols[col];
					switch	(col){
						case 'duration':
							chartItem.duration += duration;
							break;
						case 'load': 
	                        chartItem.load += Number(session.intensity || 0) * duration / 8 * 100 / 60;
							break;
						case 'perceivedload':
							chartItem.perceivedload += Number(session.perceivedeffort || 0) * duration / 8 * 100 / 60;
							break;
						case 'fatigue':
							chartItem.fatigue += ((session.sensations && session.mood) ? 11 - (Number(session.sensations) + session.mood) / 2 : 0) * duration;
							break;
						case 'sts':
						case 'lts':
						case 'tsb':
							chartItem[col] = Number(session[col] || 0);
							break;
						case 'trimphr':
							chartItem[col] += Number(session[col] || session.trimpavghr);
							break;
						case 'trimppw':
							chartItem[col] += week ? Number(session[col]) : (Number(session[col]) || Number(session.trimphr));
							break;
						case 'equivalentDistance':
							chartItem[col] += Number(session.distance || 0) * ((self.equivalentDistanceCoefficients && self.equivalentDistanceCoefficients[session.sport]) || 1.0);
							break;
						default:
							chartItem[col] += Number(session[col] || 0) * (colAtts.isDurationAverage ? duration : 1);
					}
				});
			}
		},
		buildPmcCols: function(chartItem, previousSession, currentDate, stsDailyDecay, ltsDailyDecay, stsRatio){
			var daysDifference = dutils.difference(previousSession.startdate, currentDate);
			chartItem.sts = previousSession.sts * Math.pow(stsDailyDecay, daysDifference);
			chartItem.lts = previousSession.lts * Math.pow(ltsDailyDecay, daysDifference);
			chartItem.tsb = chartItem.lts - chartItem.sts * stsRatio;
		},
		finalizeChartItem: function(chartWidget, chartItem, tableItem, presentCols, chartAtts, colsTLabel, dayOrWeek, tooltipComplement){
			presentCols.forEach(function(col){
		        var colAtts = chartAtts.cols[col], tooltipPrefix = colsTLabel[col] + ': ' ;
				switch(col){
					case 'duration': 
			            tableItem.duration = utils.transform(chartItem.duration, 'minutesToHHMM');
						chartItem.durationTooltip = tooltipPrefix + tableItem.duration  + (tooltipComplement || '');
						break;
					case 'hracwr':
						chartItem[col] = (chartItem.sts && chartItem.lts) ? chartItem.sts / chartItem.lts : 0;
					default:
						if (colAtts.isDurationAverage){
							chartItem[col] = chartItem[col] / chartItem.duration;
						}
						if (colAtts.normalizationFactor){
							chartItem[col] = chartItem[col] / colAtts.normalizationFactor[dayOrWeek];
						}
						tableItem[col] = chartItem[col].toFixed(colAtts.decimals || 1);
						chartItem[col + 'Tooltip'] = tooltipPrefix + tableItem[col] + (colAtts.tooltipUnit || '') + (tooltipComplement || '');
						if (colAtts.scalingFactor && chartWidget.get('applyscalingfactor') !== 'NO'){
							chartItem[col] = chartItem[col] / colAtts.scalingFactor[dayOrWeek];
						}
				}
			});
		},
		updateChartsLocalAction: function(sWidget, tWidget){
			var grid = tWidget.column.grid, col = tWidget.column.field, isPerformed = sWidget.valueOf('#mode') === 'performed';
			this.updateCharts(grid, isPerformed, col);
		},
		updateCharts: function(grid, isPerformed, col){
			var form = grid.form;
			this.equivalentDistanceCoefficients = utils.toObject(form.programsConfig && JSON.parse(form.programsConfig.equivalentDistance), 'sport', 'coefficient');
			(isPerformed 
				? (isPerformed === 'changed' ? ['performedloadchart', 'weekperformedloadchart', 'loadchart', 'weekloadchart'] : ['performedloadchart', 'weekperformedloadchart'])
				: ['loadchart', 'weekloadchart']).forEach(function(chartName){
			    var chartWidget = form.getWidget(chartName);
			    if (!chartWidget.get('hidden') && (!col || chartWidget.get('chartAtts').cols[col])){
			        switch(chartName){
			            case 'loadchart':
			            case 'performedloadchart':
			                grid.loadChartUtils.setProgramLoadChartValue(form, chartName);
			                break;
			            default:
			                grid.loadChartUtils.setWeekLoadChartValue(form, chartName);
			        }
			    }
			});
			form.resize();
		}
		
    });
}); 

