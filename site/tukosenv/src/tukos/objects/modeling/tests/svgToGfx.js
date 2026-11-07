"use strict";
define(["dojo/_base/declare", "dojo/_base/lang"], 
function(declare, lang){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			console.log('coucou');
			const process = async function(){
				const parser = new DOMParser();
				const xsltProcessor = new XSLTProcessor();
	
				// Load the XSLT file, example1.xsl
				const xslResponse = await fetch("/tukos/tukosenv/src/dojox/gfx/resources/svg2gfx.xsl");
				const xslText = await xslResponse.text();
				const xslStylesheet = parser.parseFromString(xslText, "application/xml");
				xsltProcessor.importStylesheet(xslStylesheet);
	
				// Load the XML file, example1.xml
				/*const xmlResponse = await fetch("example1.xml");
				const xmlText = await xmlResponse.text();*/
				const xmlText = 
				'<svg width="640" height="480" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">' +
				  '<g id="dojox.drawing.tools.custom.Vector2" transform="matrix(0.480392, 0, 0, 1, 102.48, 117)">' +
				   '<line fill="none" fill-opacity="0" id="svg_1" stroke="rgb(0, 255, 255)" stroke-linecap="round" stroke-miterlimit="4" stroke-opacity="0" stroke-width="6" x1="0" x2="100" y1="0" y2="0"/>' +
				   '<line fill="none" fill-opacity="0" id="svg_2" stroke="rgb(0, 0, 0)" stroke-linecap="round" stroke-miterlimit="4" x1="0" x2="100" y1="0" y2="0"/>' +
				   '<path d="m100,0l-29.71,-4.18l0,8.36l29.71,-4.18z" fill="rgb(255, 255, 255)" fill-opacity="0" fill-rule="evenodd" id="svg_3" stroke="rgb(0, 255, 255)" stroke-linecap="round" stroke-miterlimit="4" stroke-opacity="0" stroke-width="6"/>' +
				   '<path d="m100,0l-29.71,-4.18l0,8.36l29.71,-4.18z" fill="rgb(204, 204, 204)" fill-rule="evenodd" id="svg_4" stroke="rgb(0, 0, 0)" stroke-linecap="round" stroke-miterlimit="4"/>' +
				  '</g>' +
				'</svg>';
				const xmlDoc = parser.parseFromString(xmlText, "application/xml");
	
				const fragment = xsltProcessor.transformToFragment(xmlDoc, document);
				
				console.log(fragment);
			}
			process();
		}
	});
});
