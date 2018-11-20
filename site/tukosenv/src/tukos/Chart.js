define(["dojo/_base/declare", "dojo/_base/lang",  "dojo/dom-construct",  "dojo/dom-style", "dojo/Deferred",  "dijit/_WidgetBase", "dojox/charting/Chart",
        "dojox/charting/themes/ThreeD"/*, "dojox/charting/axis2d/Default"*/, "dojox/charting/StoreSeries", "dojo/json", "dojo/store/Observable", "dojo/store/Memory", "dstore/Memory", "dojo/ready", "tukos/utils"], 
function(declare, lang, dct, dst, Deferred, Widget, Chart, theme/*, Axis2d*/, StoreSeries, JSON, Observable, Memory, DMemory, ready, utils){
    var classesPath = {
        Default:  "dojox/charting/plot2d/", Columns: "dojox/charting/plot2d/", ClusteredColumns: "dojox/charting/plot2d/", Lines: "dojox/charting/plot2d/", Areas: "dojox/charting/plot2d/", Pie: "dojox/charting/plot2d/",
        Indicator: "dojox/charting/plot2d/", Legend: "dojox/charting/widget/", SelectableLegend: "dojox/charting/widget/", Axis2d:  "*dojox/charting/axis2d/Default", Tooltip: "dojox/charting/action2d/", ReadonlyGrid: "tukos/"
    };

	return declare(Widget, {
        
        constructor: function(args){
            args.onLoadDeferred = new Deferred();
        },
        
        postCreate: function(){
            var requiredClasses = {};
            this.inherited(arguments);
            this.store  = new Observable(new Memory({idProperty: this.idProperty || 'id'}));
            for (var plotName in this.plots){
                var requiredType = this.plots[plotName].plotType;
                requiredClasses[requiredType] = this.classLocation(requiredType);
            }
            utils.forEach(this.axes, lang.hitch(this, function(axis){
            	var requiredType = axis.type || 'Axis2d';
            	requiredClasses[requiredType] = this.classLocation(requiredType);
            }));
            utils.forEach(this.series, lang.hitch(this, function(series){
            	var requiredType = series.value.tooltip;
            	if (requiredType){
                	requiredClasses['Tooltip'] = this.classLocation('Tooltip');
            		
            	}
            }));
            if (this.legend){
                requiredClasses[this.legend.type] = this.classLocation(this.legend.type);
            }
            if (this.tableAtts){
            	requiredClasses['ReadonlyGrid'] = this.classLocation('ReadonlyGrid');
            }
            var requiredTypes = Object.keys(requiredClasses);
            require(requiredTypes.map(function(i){return requiredClasses[i];}), lang.hitch(this, function(){
                var chartStyle = this.chartStyle || {}, tableStyle = this.tableStyle || {};
            	this.chartClasses = {};
                for (var i in requiredTypes){
                    this.chartClasses[requiredTypes[i]] = arguments[i];
                }
                dst.set(this.domNode, {height: 'auto'});//or else the legend ovelaps other content

                if (this.tableAtts){
                	var table = dct.create('table', {}, this.domNode);
                	var tr = dct.create('tr', {}, table);
                	this.tableNode = dct.create('div', {style: tableStyle}, dct.create('td', {style: {width: "20%"}}, tr));
                	this.chartNode = dct.create('div', {style: chartStyle}, dct.create('td', {style: {width: "80%"}}, tr));
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
                        plotOptions.type = this.chartClasses[plotOptions.plotType];
                        this.chart.addPlot(plotName, plotOptions);
                    }
                    this.onLoadDeferred.resolve();
                }), 0);
            }));
            this.watch('style', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
            this.watch('showTable', lang.hitch(this, function(){
            	this.set('value', this.value);
            }));
        },
        
        createTableWidget: function(){
        	this.tableWidget = new this.chartClasses['ReadonlyGrid'](lang.mixin(this.tableAtts, {hidden: this.showTable !== 'yes', form: this.form, collection: new DMemory({data: []})}), this.tableNode);
        	this.tableWidget.customizationPath = this.itemCustomization || 'customization' + '.widgetsDescription.' + this.widgetName + '.atts.tableAtts.';
            this.tableWidget.on("dgrid-columnstatechange", lang.hitch(this, function(evt){
                setTimeout(lang.hitch(this, function(){this.set('value', this.value);}), 100);
            }));
            this.tableWidget.on("dgrid-columnresize", lang.hitch(this, function(evt){
                setTimeout(lang.hitch(this, function(){this.set('value', this.value);}), 100);
            }));
        },

        _setValueAttr: function(value){
            var value = value || '', store = this.store, idProperty = this.store.idProperty, kwArgs = this.kwArgs || {}, tooltips={};
            this._set("value", value);
            if (value != ''){
                store.setData(value.store);
                var sortedData = this.sortedData = store.query(kwArgs.query, kwArgs);//kwArgs.query ? store.query(kwArgs.query, kwArgs) : value.store;
                this.onLoadDeferred.then(lang.hitch(this, function(){
                    var chart = this.chart, showTable = this.showTable, tableNode = this.tableNode, width = dst.get(this.domNode, "width"), height = dst.get(this.chartNode, "height");//, tableStore = this.tableStore;
                	if (showTable === 'yes'){
                    	if (!this.tableWidget){
                    		lang.hitch(this, this.createTableWidget)();
                    	}
                		dst.set(tableNode, {display: "block", minWidth: this.tableAtts.minWidth});
                		//this.tableWidget.renderArray(value.store);
                		this.tableWidget.collection.setData(value.store);
                		this.tableWidget.refresh();
                	}else{
                		if (tableNode){
                    		dst.set(tableNode, {display: "none"});               			
                		}
                	}
                	for (var axisName in value.axes){
                            chart.addAxis(axisName, utils.mergeRecursive(this.axes[axisName], value.axes[axisName]));
                    }
                    for (var plotName in value.plots){
                        chart.addPlot(plotName, utils.mergeRecursive(this.plots[plotName], value.plots[plotName]));
                    }
                    for (var seriesName in this.series){
                        var series = this.series[seriesName];
                        chart.addSeries(seriesName, new StoreSeries(store, kwArgs, series.value), series.options);
                        if (series.value.tooltip){
                        	tooltips[seriesName] = new this.chartClasses['Tooltip'](chart, series.options.plot);
                        }
                    }
                    chart.resize(showTable ==='yes' ? width - dst.get(this.tableWidget.domNode, "width") : width, height);
                    chart.render();
                    if (this.legend && !this.legendWidget){
                        this.legendWidget = new this.chartClasses[this.legend.type](lang.mixin({chart: chart}, this.legend.options || {}), this.legendNode); 
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

