define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils", "tukos/PageManager"], 
function(declare, lang, when, ArrayIterator, utils, dutils, Pmg){
return declare(null, {
        constructor: function(args){
			this.collection = args.dailiesStore.sort([{property: 'startdate'}]);
        },
		setChartValue: function(form, chartWidgetName){
			var chartWidget = form.getWidget(chartWidgetName), hidden = chartWidget.get('hidden'), fromDate = form.valueOf('fromdate'), toDate = form.valueOf('todate');
			if (!hidden){
				dojo.ready(function(){
					var chartAtts = chartWidget.get('chartAtts'), colsAtts = chartAtts.type.cols, dayDate = fromDate, dayDateObject = dutils.parseDate(dayDate), chartItem, chartData = [],
					    grid = form.getWidget('physiopersodailies'), dayType = chartWidget.get('daytype'), filter = new grid.store.Filter(), presentCols = [], colsTLabel = {}, series = chartWidget.get('series'), 
						i = 1, dataIndex = 0, data = grid.collection.filter(filter.gte('startdate', fromDate).lte('startdate', toDate)).sort('startdate').fetchSync(), item;
					utils.forEach(series, function(content, col){
						presentCols.push(col);
						colsTLabel[col] = content.options.label;
					});
					while (dayDate <= toDate){
						chartItem = {day: dayType === 'dayoftreatment' ? i : dayDate};
						if ((data[dataIndex] || {}).startdate === dayDate){
							item = data[dataIndex];
							presentCols.forEach(function(col){
								var colAtts = colsAtts[col];
								switch	(col){
									case 'duration':
										chartItem.duration = dutils.seconds(item.duration, 'time') / 60;
									default:
										chartItem[col] = Number(item[col] || 0);
								}
								chartItem[col + 'Tooltip'] = colsTLabel[col] + ': ' + chartItem[col] + (colAtts.tooltipUnit === undefined ? '' :  colAtts.tooltipUnit);
								if (colAtts.scalingFactor){
									chartItem[col] = chartItem[col] / colAtts.scalingFactor;
								}
							});
							dataIndex += 1;
						}else{
							presentCols.forEach(function(col){
								chartItem[col] = 0;
								chartItem[col + 'Tooltip'] = colsTLabel[col] + ': ' + chartItem[col];
							});
						}
						chartData.push(chartItem);
						i += 1;
						dayDateObject.setDate(dayDateObject.getDate() + 1);
						dayDate = dutils.formatDate(dayDateObject);
					}
					chartWidget.set('value', {store: chartData, axes: {x: {title: Pmg.message(dayType, 'physiopersotreatments')}}});
				});
			}
		}
    });
}); 

