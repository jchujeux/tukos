define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dojox/charting/widget/SelectableLegend"], 
    function(declare, lang, dst, SelectableLegend){
    return declare([SelectableLegend], {

        toogle: function(plotName, index, hide){
        	this.inherited(arguments);
        	lang.setObject('customization.widgetsDescription.' + this.chartWidgetName + '.atts.series.' + this.chart.series[index].name + '.options.hidden', hide, this.form);
			this.tukosChartWidget.series[this.chart.series[index].name].options.hidden = hide;
			lang.setObject("series." + this.chart.series[index].name + ".options.hidden", hide, this.tukosChartWidget);
        },
		_addLabel: function(dyn, label){
			this.inherited(arguments);
			this.legends.forEach(function(legendNode){
				dst.set(legendNode, 'verticalAlign', 'top');
			});
		},
		refresh: function(){
			this.inherited(arguments);
		}
    });
});
