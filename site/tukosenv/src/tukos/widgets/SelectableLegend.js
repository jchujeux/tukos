define(["dojo/_base/declare", "dojo/_base/lang", "dojox/charting/widget/SelectableLegend"], 
    function(declare, lang, SelectableLegend){
    return declare([SelectableLegend], {

        toogle: function(plotName, index, hide){
        	this.inherited(arguments);
        	lang.setObject('customization.widgetsDescription.' + this.chartWidgetName + '.atts.series.' + this.chart.series[index].name + '.options.hidden', hide, this.form);
        }
    });
});
