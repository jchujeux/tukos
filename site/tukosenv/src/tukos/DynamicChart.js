"use strict";
define(["dojo/_base/declare", "dojo/_base/lang",  "dojo/dom-construct",  "dojo/dom-style", "dojo/Deferred",  "dijit/_WidgetBase", "dojox/charting/Chart",
        "dojox/charting/themes/ThreeD", "dojox/charting/StoreSeries", "dojo/store/Observable", "dojo/store/Memory"/*, "dstore/Memory"*/, "dojo/ready", "tukos/utils", "tukos/dateutils", "tukos/evalutils", "tukos/widgets/widgetCustomUtils", "tukos/PageManager"], 
function(declare, lang, dct, dst, Deferred, Widget, Chart, theme, StoreSeries, Observable, Memory/*, DMemory*/, ready, utils, dutils, evalutils, wcutils, Pmg){
    var classesPath = {
        Curves:  "*dojoFixes/dojox/charting/plot2d/Default", Columns: "dojox/charting/plot2d/", ClusteredColumns: "dojox/charting/plot2d/", Lines: "dojox/charting/plot2d/", Areas: "dojox/charting/plot2d/", Pie: "dojox/charting/plot2d/", Spider: "tukos/charting/plot2d/",
        Indicator: "dojox/charting/plot2d/", Legend: "dojox/charting/widget/", SelectableLegend: "tukos/widgets/", axis2dDefault:  "*dojox/charting/axis2d/Default", axis2dBase: "*dojox/charting/axis2d/Base", Tooltip: "dojox/charting/action2d/", BasicGrid: "tukos/",
		MouseIndicator: "dojox/charting/action2d/", MouseZoomAndPan: "dojox/charting/action2d/"
    };
	return declare(Widget, {
        
        constructor: function(args){
        	args.customizableAtts = lang.mixin({chartHeight: wcutils.sizeAtt('chartHeight'), showTable: wcutils.yesOrNoAtt('showTable'), tableWidth: wcutils.sizeAtt('tableWidth')}, args.customizableAtts);
        },
        postCreate: function postCreate(){
            const requiredClasses = this.requiredClasses = {};
            this.inherited(postCreate, arguments);
            this.store  = new Observable(new Memory({idProperty: this.idProperty || 'id'}));
            const chartStyle = this.chartStyle || {}, tableStyle = this.tableStyle || {};
            if (this.chartHeight){
            	chartStyle.height = this.chartHeight;
            	this.chartStyle = chartStyle;
            }
            dst.set(this.domNode, {height: 'auto'});//or else the legend ovelaps other content

			if (this.hasOwnProperty('showTable')){
            	const table = this.table = dct.create('table', {style: {tableLayout: 'fixed', width: '100%'}}, this.domNode), tr = dct.create('tr', {}, table);
            	this.tableNode = dct.create('div', {style: tableStyle}, dct.create('td', {style: {width: this.tableWidth || "20%"}}, tr));
            	this.chartNode = dct.create('div', {style: chartStyle}, dct.create('td', {}, tr));
				this.watch('showTable', lang.hitch(this, function(){
	            	this.set('value', this.value);
	            }));
	            this.watch('tableWidth', lang.hitch(this, function(){
	            	this.set('value', this.value);
	            }));
            }else{
                this.chartNode = dct.create('div', {style: chartStyle}, this.domNode);  
            }
            if (this.legend){
                this.legendNode = dct.create('div', {}, this.chartNode.parentNode);
            }
            if (this.tooltip){
				requiredClasses['Tooltip'] = this.classLocation('Tooltip');
			}
            if (this.mouseZoomAndPan){
				requiredClasses['MouseZoomAndPan'] = this.classLocation('MouseZoomAndPan');
			}
			if (this.mouseIndicator){
				requiredClasses['MouseIndicator'] = this.classLocation('MouseIndicator');
			}
            if (this.legend){
                requiredClasses[this.legend.type] = this.classLocation(this.legend.type);
            }
            this.watch('style', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
            this.watch('chartHeight', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
        },
        createTableWidget: function(BasicGrid){
        	this.tableWidget = new BasicGrid(lang.mixin(this.tableAtts, {dynamicColumns: true, hidden: this.showTable !== 'yes', form: this.form}), this.tableNode);
        	this.tableWidget.startup();// needed as if not present on refreshing the tab, tableWidget._started is null and the cells are not displayed
        	this.tableWidget.customizationPath = 'customization.widgetsDescription.' + this.widgetName + '.atts.tableAtts.';
            this.tableWidget.on("dgrid-columnstatechange", lang.hitch(this, function(evt){
                setTimeout(lang.hitch(this, function(){this.set('value', this.value);}), 100);
            }));
            this.tableWidget.on("dgrid-columnresize", lang.hitch(this, function(evt){
                setTimeout(lang.hitch(this, function(){this.set('value', this.value);}), 100);
            }));
        },
        resize: function resize(){
			this.inherited(resize, arguments);
			if (this.tableWidget){
 				if(this.showTable === 'yes'){
					this.tableWidget.resize();
				}
            	if (this.chart){
					const width = dst.get(this.domNode, "width"), height = this.chartHeight || dst.get(this.chartNode, "height");
            		this.chart.resize(this.showTable ==='yes' ? width - dst.get(this.tableWidget.domNode, "width") : width, height);
				}
			}
		},
        _setValueAttr: function(value){
            this._set("value", value || '');
            if (value){
            	if (!this.hasOwnProperty('chart')){
	                this.chart = new Chart(this.chartNode);
	                this.chart.setTheme(theme);	
				}else{
					const chart = this.chart;
					utils.forEach(chart.axes, function(axis, name){
						chart.removeAxis(name);
					});
					utils.forEach(chart.plots, function(plot, name){
						chart.removePlot(name);
					});
				}
            	const self = this, chart = this.chart, store = this.store, kwArgs = this.kwArgs || {query: {}}, requiredClasses = this.requiredClasses, axes = lang.clone(value.axes), plots = lang.clone(value.plots), series = lang.clone(value.series);
	            utils.forEach(plots, function(plot){
					const requiredType = plot.type;
	                requiredClasses[requiredType] = self.classLocation(requiredType);
				});
	            utils.forEach(axes, function(axis){
	            	const requiredType = axis.type || 'Default';
	            	requiredClasses[requiredType] = self.classLocation('axis2d' + requiredType);
	            });
				if (this.hasOwnProperty('showTable') && this.showTable === 'yes' && !this.hasOwnProperty('tableWidget')){
                    requiredClasses['BasicGrid'] = this.classLocation('BasicGrid');
				}
	            const requiredTypes = Object.keys(requiredClasses);
	            require(requiredTypes.map(function(i){return requiredClasses[i];}), lang.hitch(this, function(){
		        	const chartClasses = {};
		            for (let i in requiredTypes){
		                chartClasses[requiredTypes[i]] = arguments[i];
		            }
					if (this.hasOwnProperty('showTable') && this.showTable === 'yes' && !this.hasOwnProperty('tableWidget')){
						lang.hitch(this, this.createTableWidget)(chartClasses['BasicGrid']);
					}            		
                    for (let axisName in axes){
                        const axisOptions = axes[axisName];
                        if (axisOptions.tickslabel){
                            axisOptions.labelFunc = lang.hitch(this, this.getLabel, axisOptions);
                        }
                        chart.addAxis(axisName, axisOptions);
                    }
                    for (let plotName in plots){
                        const plotOptions = plots[plotName];
                        plotOptions.type = chartClasses[plotOptions.type];
						if (typeof plotOptions.styleFunc === 'string'){
							plotOptions.styleFunc = evalutils.eval(plotOptions.styleFunc);
						}
              			chart.addPlot(plotName, plotOptions);
	                    if (chartClasses['Tooltip']){
	                    	plotOptions.tooltip = new chartClasses['Tooltip'](chart, plotName);
	                    }
	                    if (chartClasses['MouseZoomAndPan']){
	                    	plotOptions.mouseZoomAndPan = new chartClasses['MouseZoomAndPan'](chart, plotName);
	                    }
                    }
	                store.setData(value.data);
	                this.sortedData = store.query(kwArgs.query, kwArgs);
                    const showTable = this.showTable, tableNode = this.tableNode, chartNode = this.chartNode, width = dst.get(this.domNode, "width"), height = this.chartHeight || dst.get(this.chartNode, "height"),
                    	tableHeight = (parseInt(height)-20) + 'px';
					if (showTable === 'yes'){
                		dst.set(this.table, {tableLayout: "fixed"});
                		dst.set(tableNode, {display: "block"});
                		dst.set(tableNode.parentNode, {width: this.tableWidth || '20%'});
                		this.tableWidget.set('maxHeight', tableHeight);
						if (value.tableColumns){
							this.tableWidget.set('columns', value.tableColumns);
						}
                		this.tableWidget.set('value', value.tableData || value.data);
                	}else{
                		if (tableNode){
                    		dst.set(tableNode, {display: "none"});   
                    		dst.set(tableNode.parentNode, {width: '0%'});
                		}
                	}
                	dst.set(chartNode, {height: height});
					if (value.resetSeries){
						chart.series = [];
						chart.runs = {};
					}
					for (let seriesName in series){
						const serie = series[seriesName];
                        chart.addSeries(seriesName, new StoreSeries(store, kwArgs, serie.value), serie.options);
                    }
					if (chartClasses['MouseIndicator'] && !chart.mouseIndicator){
						chart.mouseIndicator = new chartClasses['MouseIndicator'](chart, this.mouseIndicator.plot, this.mouseIndicator.kwArgs);
					}
                    try {
                        chart.render();
                        chart.resize(showTable ==='yes' ? width - dst.get(this.tableWidget.domNode, "width") : width, height);
                    }catch(err){
                        console.log('error while rendering or resizing chart for widget: ' + this.widgetName + ' - ' + err.message);
                    }
                    if (this.legend){
						if (!this.legendWidget){
							this.legendWidget = new chartClasses[this.legend.type](lang.mixin({chart: chart, 
								chartWidgetName: this.widgetName, form: this.form, tukosChartWidget: this}, this.legend.options || {}), this.legendNode);
							if (Pmg.isMobile()){
								this.legendWidget.set('style', {color: 'white'});
							}
						}else{
							this.legendWidget.refresh();
						}
					}
	            }));


            }
        },
        
        getLabel: function(options, formattedValue, rawValue){
            if (utils.in_array(['dateofday', 'dateofweek'], options.tickslabel) && options.firstDate){
				return dutils.formatDate(dutils.dateAdd(options.firstDate, 'day', rawValue - 1));
			}else{
				return formattedValue;
			}
            //return  this.sortedData[rawValue-1] ? this.sortedData[rawValue-1][labelCol]: '';
        },
        classLocation: function(classType){
            const classPath = classesPath[classType];
        	return classPath.charAt(0) === '*' ? classPath.substring(1) : classPath +  classType;
        }
    });
}); 

