"use strict";
define(["dojo/string", "tukos/utils"],
	function(string, utils) {
		const positionToPositionString = function(position){
			return position.length === 1 ? Math.trunc(position[0][0]) + ' ' + Math.trunc(position[0][1]) : Math.trunc(position[0]) + ' ' + Math.trunc(position[1]); 
		};
	return {
		moveTo: function(pathDescription, ...position){
			return pathDescription += 'M ' + positionToPositionString(position);
		},
		lineTo: function(pathDescription, ...position){
			return pathDescription += ' L ' + positionToPositionString(position);
		},
		qCurveTo: function(pathDescription, control, position){
			pathDescription += ' Q ' + Math.trunc(control[0]) + ' ' + Math.trunc(control[1]) + ' ' + positionToPositionString(position);
			return pathDescription;
		},
		arrowTemplate: function(atts){
			const arrowXY = {x1: '0', y1: '0', x2: '100', y2: '0'}, commonAtts = {'stroke-linecap': 'round', 'stroke-linejoin': 'miter', 'stroke-mitterLimit': "4"}, pathString = "M 100, 0, 70 -4 70 4";
			//atts.id = 'arrow' + utils.uniqueId();
			return {atts: {id: 'arrow' + utils.uniqueId(), 'stroke-linecap': 'round', 'stroke-linejoin': 'miter', 'stroke-mitterLimit': "4"}, children: [
				{tag: 'line', atts: Object.assign({fill: 'none', 'fill-opacity': 0, stroke: atts.strokeColor || "rgb(0, 255, 255)", 'stroke-opacity': '0', 'stroke-width': 6*(atts.strokeWidth || 1)}, arrowXY)},
				{tag: 'line', atts: Object.assign({fill: 'none', 'fill-opacity': 0, stroke: atts.strokeColor || "rgb(  0,   0,   0)", 'stroke-opacity': '1', 'stroke-width':    atts.strokeWidth || 1 }, arrowXY)},
				{tag: 'path', atts:               {d: pathString, fill: atts.fillColor || 'rgb(255, 255, 255)', 'fill-opacity': '0', stroke: atts.strokeColor || 'rgb(0, 255, 255)', 'stroke-opacity': '0', 'stroke-width': 6*(atts.strokeWidth || 1)}},
				{tag: 'path', atts:               {d: pathString, fill: atts.fillColor || 'rgb(204, 204, 204)', 'fill-opacity': '1', stroke: atts.strokeColor || 'rgb(0,   0,   0)', 'stroke-opacity': '1', 'stroke-width':    atts.strokeWidth || 1 }},
			]};
		},
		arrowTemplateString: function(options){//horizontal arow: x1="100" x2="200" y1="100" y2="100"
			return 	string.substitute('<line fill="none" fill-opacity="0" stroke="${stroke0255255}" stroke-opacity="0" stroke-width="${width6}" stroke-linecap="round" stroke-linejoin="miter" stroke-miterlimit="4" x1="100" y1="100" x2="200" y2="100"></line>' +
				'<line fill="none" fill-opacity="0" stroke="${stroke000}" stroke-opacity="1" stroke-width="${width1}" stroke-linecap="round" stroke-linejoin="miter" stroke-miterlimit="4" x1="100" y1="100" x2="200" y2="100"></line>' +
				'<path fill="${fill255255255}" fill-opacity="0" stroke="${stroke0255255}" stroke-opacity="0" stroke-width="${width6}" stroke-linecap="round" stroke-linejoin="miter" stroke-miterlimit="4" d="M 200 100 170.2920 95.8248 170.2920 104.1752Z" ></path>' +
				'<path fill="${fill204204204}" fill-opacity="1" stroke=${stroke000} stroke-opacity="1" stroke-width="${width1}" stroke-linecap="round" stroke-linejoin="miter" stroke-miterlimit="4" d="M 200 100 170.2920 95.8248 170.2920 104.1752Z" ></path>',
			  {stroke0255255: options.strokeColor || "rgb(0, 255, 255)", stroke000: options.strokeColor || "rgb(0, 0, 0)", fill255255255: options.fillColor || "rgb(255, 255, 255)", fill204204204: options.fillColor || "rgb(204, 204, 204)",
				width1: options.strokeWidth ? options.strokeWidth : 1, width6: options.strokeWidth ? options.strokeWidth * 6 : 6
			  });

		},
		arrow: function(group, origin, components, options){
			return group.svg(this.arrowTemplateString(options || {})).transform({origin: 'left', translate: origin.map((x) => x - 100), rotate: Math.atan(components[1] / components[0]) * 180 / Math.PI, scale: Math.sqrt(components[0] **2 + components[1]**2) / 100});
		},
		arrowTransform: function(origin, components){
			return {translate: origin, rotate: [Math.atan2(components[1], components[0]) * 180 / Math.PI], scale: Math.sqrt(components[0] **2 + components[1]**2) / 100};
		}
	}
});
