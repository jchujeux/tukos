"use strict";
define (["dojo/_base/declare", "dojox/charting/plot2d/Spider"], 
    function(declare, Spider){
    return declare([Spider], {
		addSeries: function addSeries(run){
			if (Array.isArray(run.data)){
				let data = {}, tooltips = {};
				run.data.forEach(function(item, index){
					data[item.key] = item.value;
					if (item.tooltip){
						tooltips[index] = item.tooltip;
					}
				});
				run.data = data;
				run.tooltips = tooltips;
			}
			return this.inherited(addSeries, arguments);
		},
		tooltipFunc: function(o){
			if(o.element == "spider_circle"){
				return o.run.tooltips[o.index] || (o.tdata.sname + "<br/>" + o.tdata.key + "<br/>" + o.tdata.data);
			}else{
				return null;
			}
		}
    }); 
});
