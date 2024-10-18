"use strict";
define(["dojo/dom-construct", "dojo/dom-style", "dojo/_base/lang", "dojo/Deferred", "dojo/when", "dojo/string", "dijit/registry", "dijit/focus",  "tukos/utils", "tukos/hiutils", "tukos/dateutils", "tukos/widgetUtils", "tukos/PageManager"], 
	function(dct, domstyle, lang, Deferred, when, string, registry, focusUtil, utils, hiutils, dutils, wutils, Pmg){
    return {

        render: function(plotter){
            const offsets = plotter.chart.offsets, dim = plotter.chart.dim,
	            xUserToPixels = function(xUser){
					const bounds = plotter._hScaler.bounds;
					return (xUser - bounds.from) * bounds.scale + offsets.l;
				},
	            yUserToPixels = function(yUser){
					const bounds = plotter._vScaler.bounds;
					return dim.height - offsets.b - (yUser - bounds.from) * bounds.scale;
				};
	            plotter.chart.series.forEach(function(serie){
				if (!serie.hidden){
					const data = serie.data;
					let xMin = Number.MAX_VALUE, xMax = - Number.MAX_VALUE, xAvg = 0, yAvg = 0;
					data.forEach(function(item){
						const x = item.x, y = item.y;
						xMin = Math.min(xMin, x);
						xMax = Math.max(xMax, x);
						xAvg += x;
						yAvg += y;
					});
					xAvg = xAvg / data.length;
					yAvg = yAvg / data.length;
					let numerator = 0, denominator = 0;
					data.forEach(function(item){
						const x = item.x, y = item.y;
						numerator += (y - yAvg) * (x - xAvg);
						denominator += (x - xAvg)**2;
					});
					const slope = numerator / denominator, yOrigin = yAvg - slope * xAvg, yRegression = function(x){return slope * x + yOrigin};
					let sct = 0, sce = 0;
					data.forEach(function(item){
						sct += (item.y - yAvg)**2
						sce += (yRegression(item.x) - yAvg)**2;
					});
					const r2 = sce / sct;
            		serie.group.createLine({x1: xUserToPixels(xMin), y1: yUserToPixels(yRegression(xMin)), x2: xUserToPixels(xMax), y2: yUserToPixels(yRegression(xMax))}).setStroke('blue');
            		serie.group.createLine({x1: xUserToPixels(xAvg), y1: yUserToPixels(0), x2: xUserToPixels(xAvg), y2: yUserToPixels(yRegression(xAvg))}).setStroke({color: 'blue', style: 'Dash'});
            		serie.group.createText({x: xUserToPixels(xAvg), y: yUserToPixels(0), text: xAvg.toFixed(0), align: 'middle'}).setStroke('blue');
            		serie.group.createLine({x1: xUserToPixels(xMin), y1: yUserToPixels(yAvg), x2: xUserToPixels(xAvg), y2: yUserToPixels(yAvg)}).setStroke({color: 'blue', style: 'Dash'});
            		serie.group.createText({x: xUserToPixels(xMin), y: yUserToPixels(yAvg), text: yAvg.toFixed(0), align: 'start'}).setStroke('blue');
            		serie.group.createText({x: xUserToPixels(xMax), y: yUserToPixels(yRegression(xMax) - 28), text: 'R2 = ' + r2.toFixed(2), align: 'end'}).setStroke('blue');
				}else{
					
				}
			});
        }
    }
});
