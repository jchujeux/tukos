"use strict";
define (["dojo/_base/declare", "dojox/charting/plot2d/Spider"], 
    function(declare, Spider){
    return declare([Spider], {
	addSeries: function addSeries(run){
		if (Array.isArray(run.data)){
			let data = {};
			run.data.forEach(function(item){
				data[item.key] = item.value;
			});
			run.data = data;
		}
		return this.inherited(addSeries, arguments);
	}
    }); 
});
