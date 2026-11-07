"use strict";
	define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/dom-attr", "tukos/svg/svgTukos", "tukos/svg/utils"], 
	function(declare, lang, dom, domAtt, svgTukos, svgUtils){
		    return declare(null, {
			constructor: function(args){
				lang.mixin(this, args);
			},
			test: function(parameters){
				console.log('coucou');
				const domNode = dom.byId("testSvg"), {origin, components, options} = JSON.parse(parameters);
				domAtt.set(domNode, 'innerHTML', '');
				const draw = svgTukos.SVG().addTo(domNode).size(600, 400);
				const rect = draw.rect(100, 100).attr({ fill: '#f06' });
				const arrowGroup = svgUtils.arrow(draw.group(), origin, components, options);
			}
		});
	//});
});