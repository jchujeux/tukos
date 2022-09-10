"use strict"
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/dom-construct", "dojo/colors", "dijit/_WidgetBase", "dojox/gfx",  "tukos/PageManager"], 
    function(declare, lang, on, dct, colors, Widget, gfx, Pmg){
	const valueMap =  {1: 'top', 2: 'bottomLeft', 3: 'bottomRight', 4: 'middleLeft', 5: 'middleBottom', 6: 'middleRight'};

    return declare([Widget], {
        postCreate: function postCreate(){
            this.inherited(postCreate, arguments);
			const xTriangle = this.xTriangle, yTriangle = this.yTriangle, rCircle = this.rCircle, pts = this.pts = {top: {x: xTriangle/2 + rCircle, y: rCircle}, bottomLeft: {x: rCircle, y: yTriangle + rCircle}, bottomRight:{x: xTriangle + rCircle, y: yTriangle + rCircle}, 
				  middleLeft:  {x: xTriangle/4 + rCircle, y: yTriangle/2 + rCircle},  middleBottom: {x: xTriangle/2 + rCircle, y: yTriangle + rCircle}, middleRight: {x: xTriangle*3/4 + rCircle, y: yTriangle/2 + rCircle}},
				  initialCircleCenter = this.initialCircleCenter = {dx: pts.top.x, dy: pts.bottomLeft.y * 0.75},
				  surface = gfx.createSurface(this.domNode, xTriangle + 2*rCircle, yTriangle + 2*rCircle);
 			surface.createPolyline([pts.top, pts.bottomLeft, pts.bottomRight, pts.top]).setStroke({color: "black", width: 1}).setFill('white'), 
 			surface.createPolyline([pts.top, pts.middleLeft, pts.middleRight, pts.top]).setStroke({color: "black", width: 0})
 				.setFill({type: 'linear', x1: xTriangle/2 + rCircle, y1: rCircle, x2: xTriangle/2 + rCircle, y2: yTriangle/2 + rCircle, colors: [{offset: 0, color: 'orange'}, {offset: 0.5, color: 'orange'}, {offset: 1, color: 'white'}]});
 			surface.createPolyline([pts.middleLeft,pts.bottomLeft, pts.middleBottom, pts.middleLeft]).setStroke({color: "black", width: 0}).
 				setFill({type: 'linear', x1: rCircle, y1: yTriangle + rCircle, x2: xTriangle*3/8 + rCircle, y2: yTriangle*0.75 + rCircle, colors: [{offset: 0, color: 'skyblue'}, {offset: 0.5, color: 'skyblue'}, {offset: 1, color: 'white'}]});
 			surface.createPolyline([pts.middleRight, pts.middleBottom, pts.bottomRight, pts.middleRight]).setStroke({color: "black", width: 0}).
 				setFill({type: 'linear', x1: xTriangle  +rCircle, y1: yTriangle + rCircle, x2: xTriangle*5/8 + rCircle, y2: yTriangle*0.75 + rCircle, colors: [{offset: 0, color: 'mediumseagreen'}, {offset: 0.5, color: 'mediumseagreen'}, {offset: 1, color: 'white'}]});
 			surface.createText({type: 'text', x: pts.top.x, y: pts.top.y + 40, text: this.tLabel[0], align: 'middle'}).setFill('white').setFont({size: '30px', family: 'sans-serif', weight: '700'});
 			surface.createText({type: 'text', x: pts.middleLeft.x, y: pts.middleLeft.y + 40, text: this.tLabel[1], align: 'middle'}).setFill('white').setFont({size: '30px', family: 'sans-serif', weight: '700'});
 			surface.createText({type: 'text', x: pts.middleRight.x, y: pts.middleRight.y + 40, text: this.tLabel[2], align: 'middle'}).setFill('white').setFont({size: '30px', family: 'sans-serif', weight: '700'});
 			this.circleShape = surface.createShape({type: 'circle', cx: initialCircleCenter.dx, cy: initialCircleCenter.dy, r: rCircle}).setStroke({color: "black", width: 0}).setFill('yellow');
        },
        _setValueAttr: function(value){
        	this._set("value", value);
        	if (this.circleShape){
	        	const pts = this.pts, currentTransformation = this.circleShape.getTransform();
	        	if (value){
	        		const  offset = this.initialCircleCenter;
	        		/*if (currentTransformation !== null){
						offset.dx = offset.dx - (currentTransformation.dx || 0);
						offset.dy = offset.dy - (currentTransformation.dy || 0);
					}*/
	        		this.circleShape.setTransform({dx: pts[valueMap[value]].x - offset.dx, dy: pts[valueMap[value]].y - offset.dy});
	        	}else if (currentTransformation !== null){
					this.circleShape.setTransform(null);
				}
			}
 		}
    }); 
});
