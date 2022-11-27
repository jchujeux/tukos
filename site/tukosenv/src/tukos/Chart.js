"use strict";
define(["dojo/_base/declare", "dojo/_base/lang",  "dojo/dom-construct",  "dojo/dom-style", "dojo/Deferred",  "dijit/_WidgetBase", "dojox/charting/Chart",
        "dojox/charting/themes/ThreeD", "dojox/charting/StoreSeries", "dojo/store/Observable", "dojo/store/Memory"/*, "dstore/Memory"*/, "dojo/ready", "tukos/utils", "tukos/evalutils", "tukos/widgets/widgetCustomUtils"], 
function(declare, lang, dct, dst, Deferred, Widget, Chart, theme, StoreSeries, Observable, Memory/*, DMemory*/, ready, utils, evalutils, wcutils){
    var classesPath = {
        Default:  "dojoFixes/dojox/charting/plot2d/", Columns: "dojox/charting/plot2d/", ClusteredColumns: "dojox/charting/plot2d/", Lines: "dojox/charting/plot2d/", Areas: "dojox/charting/plot2d/", Pie: "dojox/charting/plot2d/", Spider: "tukos/charting/plot2d/",
        Indicator: "dojox/charting/plot2d/", Legend: "dojox/charting/widget/", SelectableLegend: "tukos/widgets/", axis2dDefault:  "*dojox/charting/axis2d/Default", axis2dBase: "*dojox/charting/axis2d/Base", Tooltip: "dojox/charting/action2d/", BasicGrid: "tukos/",
		MouseIndicator: "dojox/charting/action2d/", MouseZoomAndPan: "dojox/charting/action2d/"
    };
	return declare(Widget, {
        
        constructor: function(args){
        	args.customizableAtts = lang.mixin({chartHeight: wcutils.sizeAtt('chartHeight'), showTable: wcutils.yesOrNoAtt('showTable'), tableWidth: wcutils.sizeAtt('tableWidth')}, args.customizableAtts);
        	args.onLoadDeferred = new Deferred();
        },
        postCreate: function postCreate(){
            var requiredClasses = {};
            this.inherited(postCreate, arguments);
            this.store  = new Observable(new Memory({idProperty: this.idProperty || 'id'}));
            for (var plotName in this.plots){
                var requiredType = this.plots[plotName].type;
                requiredClasses[requiredType] = this.classLocation(requiredType);
            }
            utils.forEach(this.axes, lang.hitch(this, function(axis){
            	var requiredType = axis.type || 'Default';
            	requiredClasses[requiredType] = this.classLocation('axis2d' + requiredType);
            }));
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
            if (this.tableAtts){
            	requiredClasses['BasicGrid'] = this.classLocation('BasicGrid');
            }
            var requiredTypes = Object.keys(requiredClasses);
            require(requiredTypes.map(function(i){return requiredClasses[i];}), lang.hitch(this, function(){
                var chartStyle = this.chartStyle || {}, tableStyle = this.tableStyle || {};
                if (this.chartHeight){
                	chartStyle.height = this.chartHeight;
                	this.chartStyle = chartStyle;
                }
            	this.chartClasses = {};
                for (var i in requiredTypes){
                    this.chartClasses[requiredTypes[i]] = arguments[i];
                }
                dst.set(this.domNode, {height: 'auto'});//or else the legend ovelaps other content

                if (this.tableAtts){
                	var table = this.table = dct.create('table', {style: {tableLayout: 'fixed', width: '100%'}}, this.domNode);
                	var tr = dct.create('tr', {}, table);
                	this.tableNode = dct.create('div', {style: tableStyle}, dct.create('td', {style: {width: this.tableWidth || "20%"}}, tr));
                	this.chartNode = dct.create('div', {style: chartStyle}, dct.create('td', {}, tr));
                }else{
                    this.chartNode = dct.create('div', {style: chartStyle}, this.domNode);  
                }
                if (this.legend){
                    this.legendNode = dct.create('div', {}, this.chartNode.parentNode);
                }
                setTimeout(lang.hitch(this, function(){//jch: tried to replace setTimeout with ready, but on refreshing tab, custom width set by chartStyle is lost. Due to offsetWidth === 0 during chart instantiation
                    this.chart = new Chart(this.chartNode);
                    this.chart.setTheme(theme);
    
                    for (var axisName in this.axes){
                        var axisOptions = this.axes[axisName];
                        if (axisOptions.labelCol){
                            axisOptions.labelFunc = lang.hitch(this, this.getLabel, axisOptions.labelCol);
                        }
                        this.chart.addAxis(axisName, axisOptions);
                    }
                    for (var plotName in this.plots){
                        var plotOptions = this.plots[plotName];
                        plotOptions.type = this.chartClasses[plotOptions.type];
						if (typeof plotOptions.styleFunc === 'string'){
							plotOptions.styleFunc = evalutils.eval(plotOptions.styleFunc);
						}
                        this.chart.addPlot(plotName, plotOptions);
	                    if (this.chartClasses['Tooltip']){
	                    	plotOptions.tooltip = new this.chartClasses['Tooltip'](this.chart, plotName);
	                    }
	                    if (this.chartClasses['MouseZoomAndPan']){
	                    	plotOptions.mouseZoomAndPan = new this.chartClasses['MouseZoomAndPan'](this.chart, plotName);
	                    }
                    }
					if (this.tableAtts){
						lang.hitch(this, this.createTableWidget)();
					}            		
                    this.onLoadDeferred.resolve();
                }), 0);
            }));
            this.watch('style', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
            if (this.tableAtts){
				this.watch('showTable', lang.hitch(this, function(){
	            	this.set('value', this.value);
	            }));
	            this.watch('tableWidth', lang.hitch(this, function(){
	            	this.set('value', this.value);
	            }));
			}
            this.watch('chartHeight', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
        },
        createTableWidget: function(){
        	this.tableWidget = new this.chartClasses['BasicGrid'](lang.mixin(this.tableAtts, {hidden: this.showTable !== 'yes', form: this.form}), this.tableNode);
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
			if (this.tableWidget && this.showTable === 'yes'){
				this.tableWidget.resize();
			}
			const width = dst.get(this.domNode, "width"), height = this.chartHeight || dst.get(this.chartNode, "height");
            if (this.chart){
            	this.chart.resize(this.showTable ==='yes' ? width - dst.get(this.tableWidget.domNode, "width") : width, height);
			}
		},
        _setValueAttr: function(value){
            var value = value || '', store = this.store, kwArgs = this.kwArgs || {query: {}}, colsToExclude = this.colsToExclude ? JSON.parse(this.colsToExclude) : [], series = value.resetSeries ? value.series : utils.mergeRecursive(this.series, value.series);
            this._set("value", value);
            if (value && value.data){
                store.setData(value.data);
                this.sortedData = store.query(kwArgs.query, kwArgs);
                this.onLoadDeferred.then(lang.hitch(this, function(){
                    var chart = this.chart, showTable = this.showTable, tableNode = this.tableNode, chartNode = this.chartNode, width = dst.get(this.domNode, "width"), height = this.chartHeight || dst.get(this.chartNode, "height"),
                    	tableHeight = (parseInt(height)-20) + 'px';
                	if (this.tableAtts){
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
	                    		//dst.set(this.table, {tableLayout: "auto"});
	                		}
	                	}
					}
                	dst.set(chartNode, {height: height});
                	if (value.resetAxes){
						chart.axes = {};
					}
					for (var axisName in value.axes){
                            chart.addAxis(axisName, utils.mergeRecursive(this.axes[axisName], value.axes[axisName]));
                    }
					for (let axisName in this.chart.axes){
						for (const extremum of ['min', 'max']){
							let axisExtremum = this[axisName+ 'custom' + extremum];
							if (axisExtremum){
								this.chart.axes[axisName].opt[extremum] = axisExtremum;
							}
						}
					}
					for (var plotName in value.plots){
                        chart.addPlot(plotName, utils.mergeRecursive(this.plots[plotName], value.plots[plotName]));
                    }
					if (!utils.empty(colsToExclude)){
						utils.forEach(this.chart.axes, function(axis){
							if (axis.vertical){
								axis.opt.title = '';
							}
						});
					}
					if (value.resetSeries){
						chart.series = [];
						chart.runs = {};
					}
					for (var seriesName in series){
                        if(utils.in_array(seriesName, colsToExclude)){
							chart.removeSeries(seriesName);
						}else{
							var serie = series[seriesName];
	                        chart.addSeries(seriesName, new StoreSeries(store, kwArgs, serie.value), serie.options);
							if (!utils.empty(colsToExclude)){
								this.chart.axes[this.plots[serie.options.plot].vAxis].opt.title += serie.options.legend + ' ';
							}

						}
                    }
					if (this.chartClasses['MouseIndicator']){
						this.chart.mouseIndicator = new this.chartClasses['MouseIndicator'](this.chart, this.mouseIndicator.plot, this.mouseIndicator.kwArgs);
					}
                    try {
                        chart.render();
                        chart.resize(showTable ==='yes' ? width - dst.get(this.tableWidget.domNode, "width") : width, height);
                    }catch(err){
                        console.log('error while rendering or resizing chart for widget: ' + this.widgetName);
                    }
                    if (this.legend){
						if (!this.legendWidget){
							this.legendWidget = new this.chartClasses[this.legend.type](lang.mixin({chart: chart, 
								chartWidgetName: this.widgetName, form: this.form, tukosChartWidget: this}, this.legend.options || {}), this.legendNode);
						}else{
							this.legendWidget.refresh();
						}
					}
                }));
            }
        },
        
        getLabel: function(labelCol, formattedValue, rawValue){
            return  this.sortedData[rawValue-1] ? this.sortedData[rawValue-1][labelCol]: 'nolabel';
        },
        classLocation: function(classType){
            var classPath = classesPath[classType];
        	return classPath.charAt(0) === '*' ? classPath.substring(1) : classPath +  classType;
        }
    });
}); 

